<?php
     /*
     Plugin Name: Google+Blog
     Plugin URI: http://www.minimali.se/google-plus-blog-for-wordpress/
     Description: A plugin to import your posts from Google+.
     Version: 1.4.2
     Author: Daniel Treadwell
     Author URI: http://www.minimali.se/
     */

include(plugin_dir_path(__FILE__).'options.php');
require_once(ABSPATH . 'wp-admin/includes/image.php');

@date_default_timezone_set(get_option('timezone_string'));
@define('WP_POST_REVISIONS', 3);
define('FOOTER', "<br /><br /><i>Post imported by <a href='http://www.minimali.se/google-plus-blog-for-wordpress/'>Google+Blog for WordPress</a>.</i>");

// remove_filter('template_redirect', 'redirect_canonical');
if (!get_option('gpb_ignore_canonical', false))
    remove_action('wp_head','rel_canonical');

function gpb_canonical()
{
    global $post;

    if (get_option('gpb_ignore_canonical', false))
        return;

    if (is_single())
    {
        $url = get_post_meta($post->ID,'_googleplus_url', true);
        if ($url)
        {
            echo "<link rel='canonical' href='$url' />\n";
        }
        else
        {
            rel_canonical();
            // add_action('wp_head','rel_canonical');
        }
    }
}


function gpb_avatar($avatar, $comment, $size, $default, $alt)
{
    if (is_object($comment))
        if ($comment->comment_author_IP == 'Google+' && $comment->comment_author_email)
            $avatar = "<img alt='$comment->comment_author' src='$comment->comment_author_email' class='avatar avatar-{$size} photo avatar-default' height='{$size}' width='{$size}' />";
    return $avatar;
}

register_activation_hook(__FILE__, 'gpb_activate');
register_deactivation_hook(__FILE__,'gpb_deactivate');

add_action('gpb_hook','gpb_import');
add_action('admin_notices', 'gpb_notices');
add_filter('get_avatar', 'gpb_avatar', 10, 5);
add_action('init', 'gpb_canonical');
add_action('wp_head', 'gpb_canonical');

function gpb_activate()
{
    add_option('gpb_activity_log', array(), '', 'no');
    // add_option('gpb_activity_log', array(), '', 'no');
    update_option('gpb_errors', array());
    // update_option('gpb_is_running', false);

    wp_clear_scheduled_hook('gpb_hook');
    wp_schedule_event(time() + 60, 'hourly', 'gpb_hook');
}


function gpb_deactivate()
{
    // update_option('gpb_is_running', false);
    wp_clear_scheduled_hook('gpb_hook');
}



function gpb_request($url)
{
    if (ini_get('allow_url_fopen') && in_array('https', stream_get_wrappers()))
    {
        return file_get_contents($url);
    }
    else
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        $r = curl_exec($ch);
        curl_close($ch);
        return $r;
    }
}

function gpb_notices()
{
    foreach (get_option('gpb_errors', array()) as $error)
        echo "<div id='message' class='error'><p><strong>Google+Blog</strong> $error</p></div>";
    update_option('gpb_errors', array());
}

function gpb_log($message)
{
    $timestamp = date('Y-m-d H:i:s');
    $log = get_option('gpb_activity_log', array());
    if (count($log) > 9)
        array_shift($log);
    array_push($log, "[$timestamp] $message");
    update_option('gpb_activity_log', $log);
}

function gpb_error($message)
{
    $errors = get_option('gpb_errors', array());
    array_push($errors, $message);
    update_option('gpb_errors', $errors);
}

function gpb_check_settings()
{
    if (!get_option('gpb_profile_id'))
    {
        gpb_error("To start importing posts from Google+, head over to the settings page.");
        return false;
    }
    return true;
}


function gpb_import()
{
    if (!gpb_check_settings())
        return;

    // if (get_option('gpb_is_running', false))
    // {
    //     gpb_log('Attempted to start when already running import.');
    //     return;
    // }

    // update_option('gpb_is_running', true);
    gpb_log('Running timed sync of Google+ posts.');
    $x = 0;
    $counts = array('U' => 0, 'N' => 0, 'I' => 0);
    $authors = get_option('gpb_profile_author');
    $img_resolution = get_option('gpb_image_resolution') ? get_option('gpb_image_resolution') : 1600;
    foreach (get_option('gpb_profile_id') as $profile_id)
    {
        $counts = array('U' => 0, 'N' => 0, 'I' => 0);

        $profile_id = trim($profile_id);
        $post_count = 0;
        $page_token = '';

        gpb_log("Attempting import for $profile_id.");

        do
        {
            $maxResults = get_option('gpb_post_limit') > 100 ? 100 : get_option('gpb_post_limit');
            $r = gpb_request("https://www.googleapis.com/plus/v1/people/$profile_id/activities/public?alt=json&pp=1&key=".get_option('gpb_api_key')."&maxResults=$maxResults&pageToken=$page_token</small>");

            if (!$r)
            {
                gpb_error("Unable to fetch posts, please ensure you have entered the correct API Key and Google+ Profile ID.<br /><small>https://www.googleapis.com/plus/v1/people/$profile_id/activities/public?alt=json&pp=1&key=".get_option('gpb_api_key')."&maxResults=$maxResults&pageToken=$page_token</small>");
                // update_option('gpb_is_running', false);
                return;
            }
            $r = json_decode($r);
            $page_token = $r->nextPageToken;
            if (isset($r->items))
            {
                foreach ($r->items as $item)
                {
                    if (in_array($item->provider->title, array('Google+', 'Mobile', 'Photos', 'Google Reader', 'Reshared Post', 'Community')) && $post_count <= get_option('gpb_post_limit'))
                    {

                        $p = new GPBPost($item->id, $item->url, $item->title, $item->verb, $item->provider->title,
                                         date('Y-m-d H:i:s', @strtotime(@$item->published)), @$item->object->resharers->totalItems);
                        if ($item->object->objectType == 'activity')
                        {
                            $p->content = @$item->object->content;
                            $p->author_name = @$item->object->actor->displayName;
                            $p->author_url = @$item->object->actor->url;
                            $p->author_image = @$item->object->actor->image->url;
                            $p->annotation = @str_replace('<br>', '<br />', @$item->annotation);
                        }
                        else
                        {
                            $p->content = @$item->object->content;
                        }

                        if ($item->object->attachments)
                        {
                            foreach ($item->object->attachments as $attachment)
                            {
                                $a = new GPBAttachment($p->id.'-'.$attachment->url, $attachment->objectType, $attachment->url, $attachment->displayName);

                                switch ($attachment->objectType)
                                {
                                    case 'photo':
                                        $a->title = @$attachment->displayName;
                                        $a->url = @$attachment->url;
                                        $a->image_url = $attachment->fullImage->url && strlen($attachment->fullImage->url) > 10 ? $attachment->fullImage->url : $attachment->image->url;
                                        $a->image_url = preg_replace('/(w\d+-h\d+(-p)*|s0-d)\//','', $a->image_url) . "?imgmax=$img_resolution";
                                        $a->thumbnail_url = $attachment->image->url ? $attachment->image->url : $attachment->fullImage->url;
                                    break;
                                    case 'album':
                                        foreach ($attachment->thumbnails as $thumbnail)
                                        {
                                            $t->thumbnail_url = preg_replace('/w\d+-h\d+(-p)*\//','', $thumbnail->image->url);
                                            $t->url = $thumbnail->url;
                                            $t = new GPBAttachment($p->id.'-t-'.$thumbnail->url, 'photo', $t->url, '');
                                            $t->image_url = preg_replace('/w\d+-h\d+(-p)*\//','', $thumbnail->image->url) . "?imgmax=$img_resolution";
                                            $p->attachments[] = $t;
                                        }
                                        $a->type = 'photo-album';

                                    break;
                                    case 'photo-album':
                                        $a->title = $attachment->displayName;
                                    break;
                                    case 'video':
                                        if (substr(@strtolower($attachment->displayName), -4, 4) == '.mov')
                                        {
                                            $a->type = 'photo';
                                            $a->thumbnail_url = $a->image_url = preg_replace('/s0\-d/','', $attachment->image->url);
                                        }
                                        elseif (strstr($attachment->url, 'vimeo.com')) {
                                            $a->url = str_replace('www.vimeo.com', 'player.vimeo.com/video', $attachment->url);
                                            $a->url = str_replace('http://vimeo.com', 'http://player.vimeo.com/video', $attachment->url);
                                        }
                                        elseif (@$attachment->embed->url && @strstr(@$attachment->embed->url, 'youtube.com')) {
                                            $a->url = str_replace('/v/', '/embed/', $attachment->embed->url);
                                        }
                                        else {
                                            $a->url = str_replace('watch?v=', 'embed/', str_replace('&autoplay=1','', $attachment->url));
                                            $a->thumbnail_url = @$attachment->image->url;
                                        }
                                    break;
                                    case 'article':
                                        $a->article_snippet = @$attachment->content;
                                        $a->thumbnail_url = @$attachment->image->url;
                                    break;
                                }
                                array_push($p->attachments, $a);
                            }
                        }

                        if (!get_option('gpb_import_tag') || in_array(get_option('gpb_import_tag'), explode(', ', $p->hashtags())))
                        {
                            if (!get_option('gpb_ignore_tag') || !in_array(get_option('gpb_ignore_tag'), explode(', ',$p->hashtags())))
                                $counts[gpb_create_post($p, $authors[$x])]++;
                        }
                    }
                    $post_count++;
                }
            }
            elseif ($post_count == 0)
            {
                gpb_log("There were no posts to fetch for Profile ID '$profile_id' on Google+.");
            }
        }
        while ($page_token != '' && $post_count < get_option('gpb_post_limit'));
        gpb_log("Imported $counts[N] posts, updated $counts[U] posts, and ignored $counts[I] posts for Google+ Profile ID '$profile_id'.");
        $x++;
    }
    // update_option('gpb_is_running', false);

}

function gpb_import_comments($post_id, $wp_post_id)
{
    global $wpdb;
    $comment_ids = array();
    $page_token = '';
    $comment_total = 0;

    $comments = $wpdb->get_results( "SELECT comment_agent,comment_author_IP FROM $wpdb->comments WHERE comment_post_ID='$wp_post_id' AND comment_author_IP='Google+'");
    foreach ($comments as $c)
        $comment_ids[] = $c->comment_agent;

    do
    {
        $r = gpb_request("https://www.googleapis.com/plus/v1/activities/$post_id/comments?alt=json&pp=1&key=".get_option('gpb_api_key')."&maxResults=100&pageToken=$page_token");

        if (!$r)
        {
            gpb_log("Could not fetch comments for post '$post_id'.");
        }
        else
        {
            $r = json_decode($r);
            $page_token = $r->nextPageToken;
            if (isset($r->items))
            {
                foreach ($r->items as $item)
                {
                    if (!in_array($item->id,$comment_ids))
                    {
                        $c = new GPBComment($item->id, $post_id, @date('Y-m-d H:i:s',@strtotime(@$item->published)),
                                       $item->actor->id, $item->actor->displayName, $item->actor->image->url,
                                       $item->actor->url, $item->object->content);
                        gpb_create_comment($c, $wp_post_id);
                        $comment_total++;
                    }
                }
            }
        }
    }
    while ($page_token != '');

    // gpb_log("Imported $comment_total comments");
}

function gpb_create_post($post, $author)
{
    global $wpdb;
    $wp_post = array(
        'comment_status' => 'open',
        'post_content' => $post->getContent(),
        'post_status' => strtolower(get_option('gpb_post_status')), //'publish',
        'post_title' => $post->getTitle(), //The title of your post.
        'post_type' => 'post',
        'post_author' => $author,
        'post_date' => $post->published,
        'post_date_gmt' => $post->published_gmt,
        'post_category' => get_option('gpb_post_categories'),
        'tags_input' => get_option('gpb_post_tags'),
        'filter' => true
    );
    $hashtags = $post->hashtags();
    if (strlen($hashtags) > 0)
        $wp_post['tags_input'] .= ', '.$hashtags;

    $is_trashed = get_option('gpb_import_trashed') ? false : ($wpdb->get_results("SELECT * FROM $wpdb->postmeta JOIN $wpdb->posts ON $wpdb->posts.ID = $wpdb->postmeta.post_id WHERE meta_key='_googleplus_id' AND meta_value='$post->id' AND post_status = 'trash' LIMIT 1") ? true : false);


    $old_posts = $wpdb->get_results("SELECT * FROM $wpdb->postmeta JOIN $wpdb->posts ON $wpdb->posts.ID = $wpdb->postmeta.post_id WHERE meta_key='_googleplus_id' AND meta_value='$post->id' AND post_status != 'trash' LIMIT 1");

    if ($old_posts)
    {
        $old_post = $old_posts[0];
        $wp_post['ID'] = $old_post->ID;

        if (get_option('gpb_post_overwrite') && !$is_trashed)
        {
            $wp_post['post_category'] = array();
            $categories = get_the_category($old_post->ID);

            foreach ($categories as $category)
                $wp_post['post_category'][] = $category->cat_ID;

            // Handle updates gracefully by not reverting the post status after it has changed (or if the latest status is publish).
            if (strtolower(get_option('gpb_post_status')) == 'publish' || strtolower($old_post->post_status) == 'publish')
            {
                $wp_post['post_status'] = 'publish';
            }
            elseif (strtolower($old_post->post_status) != strtolower(get_option('gpb_post_status')))
            {
                $wp_post['post_status'] = $old_post->post_status;
            }
        }

        $post_id = $old_post->ID;
        $updated = true;
    }

    kses_remove_filters();
    if (!$updated && !$is_trashed) {# New post
        $post_id = wp_insert_post($wp_post, false);
        if ($post_id == 0) gpb_log("Problem importing post: $wp_post[post_title]");

        if (get_option('gpb_download_and_feature') && ($thumbnail_url = $post->getArticlePhoto()) !== '') {
          gpb_create_thumbnail($thumbnail_url, $post_id);
        }

    }
    elseif ($updated && get_option('gpb_post_overwrite') && !$is_trashed) { # Update
        $post_id = wp_update_post($wp_post);
        if (get_option('gpb_download_and_feature') && ($thumbnail_url = $post->getArticlePhoto()) !== '' && !has_post_thumbnail($post_id)) {
          gpb_create_thumbnail($thumbnail_url, $post_id);
        }
    }
    kses_init_filters();

    if ($post_id > 0 && !$is_trashed)
    {
        add_post_meta($post_id, '_googleplus_id', $post->id, true);
        add_post_meta($post_id, '_googleplus_url', $post->url, true);

        gpb_import_comments($post->id, $post_id);

        return $updated ? (get_option('gpb_post_overwrite') ? 'U' : 'I') : 'N';
    }
    return 'I';
}

function gpb_create_thumbnail($url, $post_id)
{
  $extension_lookup = array('image/jpeg' => '.jpg',
                            'image/png' => '.png',
                            'image/gif' => '.gif');
  $image = file_get_contents($url);
  $filename   = str_replace(array('%','?'),'', urldecode(basename($url)));
  $upload_dir = wp_upload_dir();

  if( wp_mkdir_p($upload_dir['path'])) {
    $file = $upload_dir['path'] . '/' . $filename;
  } else {
    $file = $upload_dir['basedir'] . '/' . $filename;
  }
  file_put_contents($file, $image);
  $wp_filetype = wp_check_filetype_and_ext($file, $filename);
  $file_info = getimagesize($file);
  if (key_exists($file_info['mime'], $extension_lookup)) {
    rename($file, $file.$extension_lookup[$file_info['mime']]);
    $file = $file.$extension_lookup[$file_info['mime']];
    $attachment = array(
    'post_mime_type' => $file_info['mime'],
    'post_title'     => sanitize_file_name( $filename ),
    'post_content'   => '',
    'post_status'    => 'inherit'
    );
    $attach_id = wp_insert_attachment($attachment, $file, $post_id);
    set_post_thumbnail($post_id, $attach_id);

    $attach_data = wp_generate_attachment_metadata( $attach_id, $file );
    wp_update_attachment_metadata( $attach_id, $attach_data );
  }
}

function gpb_create_comment($comment, $wp_post_id)
{

    $wp_comment = array(
        'comment_post_ID' => $wp_post_id,
        'comment_author' => $comment->author_name,
        'comment_author_email' => $comment->author_avatar,
        'comment_author_url' => $comment->author_url,
        'comment_content' => $comment->content,
        'type' => 'comment',
        'comment_parent' => 0,
        'user_id' => 0,
        'comment_author_IP' => 'Google+',
        'comment_agent' => $comment->id,
        'comment_date' => $comment->published,
        'comment_date_gmt' => $comment->published_gmt,
        'comment_approved' => 1
    );

    $comment_id = wp_insert_comment($wp_comment, true);
}


class GPBPost {

    public $id;
    public $title;
    public $content;

    public $verb;

    public $author_name;
    public $author_url;
    public $author_image;

    public $annotation;

    public $attachments = array();

    public $published;
    public $published_gmt;

    public $reshares;

    public function __construct($id, $url, $title, $verb, $provider, $published_date, $reshares)
    {
        $this->id = $id;
        $this->url = $url;
        $this->title = $title;
        $this->verb = $verb;
        $this->provider = $provider;
        $this->published = $published_date;
        $this->published_gmt = gmdate('Y-m-d H:i:s', strtotime($published_date));

        $this->reshares = $reshares;

        $this->attachments = array();
        $this->annotation = '';
    }


    function getTitle()
    {
        switch ($this->verb)
        {
            case 'share':
                if (!trim(strip_tags($this->annotation)))
                {
                    $this->annotation = $this->content;
                }

                if (strpos($this->annotation, '<br />') > 0 && strpos($this->annotation, '<br />') < 80)
                {
                    return rtrim(strip_tags(substr($this->annotation,0, strpos($this->annotation, '<br />'))),'.');
                }
                elseif (strpos(strip_tags($this->annotation), '. ') > 0 && strpos(strip_tags($this->annotation), '. ') < 80)
                {
                    return rtrim(substr(strip_tags($this->annotation),0, strpos(strip_tags($this->annotation), '. ')),'.');
                }
                else
                {
                    if (strpos($this->annotation, '<br />') > 0)
                    {
                        return safe_truncate(strip_tags(substr($this->annotation,0, strpos($this->annotation, '<br />'))), 80);
                    }
                    else
                    {
                        return safe_truncate(strip_tags($this->annotation), 80);
                    }
                }
            break;
            default:
                if (strpos($this->content, '<br />') > 0 && strpos($this->content, '<br />') < 80)
                {
                    return rtrim(strip_tags(substr($this->content,0, strpos($this->content, '<br />'))),'.');
                }
                elseif (strpos(strip_tags($this->content), '. ') > 0 && strpos(strip_tags($this->content), '. ') < 80)
                {
                    return rtrim(substr(strip_tags($this->content),0, strpos(strip_tags($this->content), '. ')),'.');
                }
                else
                {
                    if (strpos($this->content, '<br />') > 0)
                    {
                        return safe_truncate(strip_tags(substr($this->content,0, strpos($this->content, '<br />'))), 80);
                    }
                    else
                    {
                        return safe_truncate(strip_tags($this->content), 80);
                    }
                }
            break;
        }
    }

    function getContent()
    {
        $content = '';
        $content_video = '';
        $content_album = '';
        $content_images = '';
        $content_article = '';
        $gallery_images = '';
        switch ($this->verb)
        {
            case 'share':
                if (strpos($this->annotation, '<br />') > 0 && strpos($this->annotation, '<br />') < 80)
                {
                    $content = '<div class="gpb-content">'.substr($this->annotation, strpos($this->annotation, '<br />')+6)."<br /><br /><strong>Reshared post from +<a href='$this->author_url'>$this->author_name</a></strong><blockquote>$this->content</blockquote></div>";
                }
                elseif (strpos(strip_tags($this->annotation), '. ') > 0 && strpos(strip_tags($this->annotation), '. ') < 80)
                {
                    $content = '<div class="gpb-content">'.trim(substr($this->annotation,strpos(strip_tags($this->annotation), '. ')+2))."<br /><br /><strong>Reshared post from +<a href='$this->author_url'>$this->author_name</a></strong><blockquote>$this->content</blockquote></div>";
                }
                else
                {
                    $content = '<div class="gpb-content">'.$this->annotation."<br /><br /><strong>Reshared post from +<a href='$this->author_url'>$this->author_name</a></strong><blockquote>$this->content</blockquote></div>";
                }
            break;
            default:
                if (strpos($this->content, '<br />') > 0 && strpos($this->content, '<br />') < 80)
                {
                    $content = '<div class="gpb-content">'.substr($this->content,strpos($this->content, '<br />')+6).'</div>';
                }
                elseif (strpos($this->content, '. ') > 0 && strpos($this->content, '. ') < 80)
                {
                    $content = '<div class="gpb-content">'.trim(substr($this->content,strpos($this->content, '. ')+2)).'</div>';
                }
                else
                {
                    $content = '<div class="gpb-content">'.$this->content.'</div>';
                }
            break;

        }

        $firstPhoto = true;
        foreach ($this->attachments as $attachment)
        {

            switch ($attachment->type)
            {
                case 'photo':
                    if ($firstPhoto)
                    {
                        $content_images = "<div class='gpb-photo-primary'><br /><img src='$attachment->image_url' border='0' style='max-width:100%;' /></div><br />";
                        // $content_images = "<div class='gpb-photo-primary'><br /><img src='$attachment->image_url' border='0' /><br /><span>$attachment->title</span></div>";
                        $firstPhoto = false;
                    }
                    else
                    {
                        if ($attachment->thumbnail_url)
                            $gallery_images .= "<div style='height:60px;width:60px;overflow:hidden;margin:5px 5px 0px 0px;display:inline-block;'><a href='$attachment->url'><img style='max-width:none;' src='$attachment->thumbnail_url' border='0' /></a></div>";
                    }
                break;
                case 'photo-album':
                    $content_album = "<p class='gpb-album-title' style='clear:both;'><a href='$attachment->url'>In album $attachment->title</a></p>";
                break;
                case 'video':
                    if (@substr(@$attachment->url,0,4) == 'http')
                    {
                        $content_video = "<div class='gpb-video'><iframe type='text/html' width='100%' height='385' src='$attachment->url' frameborder='0'></iframe></div>";
                    }
                break;
                case 'article':
                    // if ($article_image = $this->getArticleThumbnail())
                    if (@$attachment->thumbnail_url)
                    {
                        $content_article = "<p class='gpb-article' style='clear:both;'>
                                                <div style='height:120px;width:120px;overflow:hidden;float:left;margin-top:0px;padding-top:0px;margin-right:10px;vertical-align:top;text-align:center;clear:both;'>
                                                    <img style='max-width:none;' src='".$attachment->thumbnail_url."' border='0' />
                                                </div>
                                                <a href='$attachment->url'>$attachment->title</a><br />
                                                $attachment->article_snippet<br />
                                            </p>";
                    }
                    else
                    {
                        $content_article = "<p class='gpb-article' style='clear:both;'>
                                                <p style='margin-bottom:5px;'><strong>Embedded Link</strong></p>
                                                <a href='$attachment->url'>$attachment->title</a><br />
                                                $attachment->article_snippet<br />
                                            </p>";
                    }

                break;
            }
        }

        $content_reshare_link = $content_post_link = "";

        if (get_option('gpb_display_reshares') && $this->reshares > 0)
        {
            $content_reshare_link = "<span class='gpb-reshare'><br />This post has been reshared $this->reshares times on <a href='$this->url' target='_new'>Google+</a><br /></span>";
        }
        if (get_option('gpb_display_linkback'))
        {
            $content_post_link = "<a class='gpb-linkback' href='$this->url' target='_new'>View this post on Google+</a>";
        }
        $content_links = "<p class='gpb-links' style='clear:both;'>$content_reshare_link $content_post_link</p>";
        if ($gallery_images)
            $gallery_images = "<div class='gpb-photo-thumbnail'>$gallery_images</div><br />";

        if (!get_option('gpb_album_linkback'))
            $content_album = '';

        if (get_option('gpb_photo_top'))
            return $content_video.$content_album.$content_images.$gallery_images.$content.$content_article.$content_links.FOOTER;
        return $content.$content_video.$content_album.$content_images.$gallery_images.$content_article.$content_links.FOOTER;
    }

    function hasArticle()
    {
        foreach ($this->attachments as $attachment)
        {
            if ($attachment->type == 'article')
            {
                return true;
            }
        }
        return false;
    }

    function hashtags()
    {
        $tags = '';
        preg_match_all("/(?:#)([\w\+\-]+)(?=\s|\.|<|$)/", $this->content.$this->annotation, $matches);
        if (@count($matches))
        {
            foreach ($matches[0] as $match)
            {
                $tags .= ', '. str_replace('#','', trim($match));
            }
        }

        return $tags;

    }

    function getArticleThumbnail()
    {
        foreach ($this->attachments as $attachment)
        {
            if ($attachment->type == 'photo')
            {
                return $attachment->thumbnail_url;
            }
        }
        return '';
    }

    function getArticlePhoto()
    {
        foreach ($this->attachments as $attachment)
        {
            if ($attachment->type == 'photo')
            {
                return $attachment->image_url;
            }
        }
        return '';
    }

}


// class GooglePlusBlogPostAttachment
// {
//  public $type;
//  public $id;

//  public $title; #displayName
//  public $url; #url

//  public $article_snippet; #content

//  public $image_url; #image/url
//  public $thumbnail_url; #image/url
// }

// class GooglePlusBlogComment
// {
//  public $id;
//  public $post_id;
//  public $content;
//  public $published;
//  public $published_gmt;

//  public $author_id;
//  public $author_name;
//  public $author_avatar;
//  public $author_url;
// }


class GPBAttachment {

    public $id;
    public $type;

    public $title;
    public $url;
    public $article_snippet;
    public $image_url;
    public $thumbnail_url;

    public function __construct($id, $type, $url, $title)
    {
        $this->id = $id;
        $this->type = $type;
        $this->url = $url;
        $this->title = $title;
    }
}

class GPBComment {

    public $id;
    public $post_id;
    public $content;
    public $published;
    public $published_gmt;

    public $author_id;
    public $author_name;
    public $author_avatar;
    public $author_url;

    public function __construct($id, $post_id, $published_date, $author_id, $author_name, $author_avatar, $author_url, $content)
    {
        $this->id = $id;
        $this->post_id = $post_id;
        $this->published = date('Y-m-d H:i:s', strtotime($published_date));
        $this->published_gmt = gmdate('Y-m-d H:i:s', strtotime($published_date));
        $this->author_id = $author_id;
        $this->author_name = $author_name;
        $this->author_avatar = $author_avatar;
        $this->author_url = $author_url;

        $this->content = $content;
    }
}


function safe_truncate($input, $length)
{
    if (strlen($input) <= $length)
    {
        return $input;
    }
    else
    {
        if (false !== ($endpoint_location = strpos($input, ' ', $length)))
        {
            if ($endpoint_location < strlen($input) - 1)
            {
                $input = substr($input, 0, $endpoint_location);
            }
        }
        else
        {
            $input = substr($input, 0, $length);
        }
        return $input.'...';
    }
}
?>

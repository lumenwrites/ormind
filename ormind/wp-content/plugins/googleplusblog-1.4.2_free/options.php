<?php
define('VERSION', "1.4.1");

require_once(plugin_dir_path(__FILE__).'googleplusblog.php');
// require_once(plugin_dir_path(__FILE__).'googleplusblog.php');
add_action('admin_menu', 'gpb_create_menu');
function gpb_create_menu() {
    add_menu_page('Google+Blog', 'Google+Blog', 'administrator', __FILE__, 'gpb_settings_page');
    add_action('admin_init', 'gpb_register_settings');
}

add_action('admin_notices', 'gpb_head');

function gpb_head() {
    global $wp_filter;
    // echo '<pre>'.var_export( $wp_filter['save_post'],true).'</pre>';
}


function gpb_register_settings() {
    register_setting('gpb_options', 'gpb_api_key');
    register_setting('gpb_options', 'gpb_profile_id', 'multiple_option_validate');
    register_setting('gpb_options', 'gpb_profile_author', 'multiple_option_validate');

    register_setting('gpb_options', 'gpb_post_limit');
    register_setting('gpb_options', 'gpb_post_overwrite', 'checkbox_validate');
    register_setting('gpb_options', 'gpb_ignore_canonical', 'checkbox_validate');

    register_setting('gpb_options', 'gpb_import_trashed', 'checkbox_validate');
    register_setting('gpb_options', 'gpb_display_linkback', 'checkbox_validate');
    register_setting('gpb_options', 'gpb_display_reshares', 'checkbox_validate');
    register_setting('gpb_options', 'gpb_photo_top', 'checkbox_validate');
    register_setting('gpb_options', 'gpb_album_linkback', 'checkbox_validate');
    register_setting('gpb_options', 'gpb_download_and_feature', 'checkbox_validate');
    register_setting('gpb_options', 'gpb_image_resolution', 'resolution_validate');

    register_setting('gpb_options', 'gpb_import_tag');
    register_setting('gpb_options', 'gpb_ignore_tag');

    register_setting('gpb_options', 'gpb_post_status');
    register_setting('gpb_options', 'gpb_post_categories', 'multiple_option_validate');
    register_setting('gpb_options', 'gpb_post_tags');
    register_setting('gpb_options', 'gpb_run_now', 'check_for_init');
}

function multiple_option_validate($input) { return $input ? array_values(array_filter($input)) : array(); }
function checkbox_validate($input) { return $input == '1' ? true : false; }
function resolution_validate($input) { return $input < 1024 ? 1024 : $input; }
function check_for_init($input) { return $input == '1' ? wp_schedule_single_event(time(), 'gpb_hook') : ''; }
// function post_categories_validate($input) { mail('daniel@djt.id.au', 'test', var_dump($input)); }

function gpb_settings_page() {
    $next_run = round((wp_next_scheduled('gpb_hook')-time())/60,0);
    $next_run = $next_run > 0 ? "Next import will happen in $next_run minutes" : 'Running Now';

?>
<div class="wrap">
<h2>Google+Blog for WordPress</h2>
Options relating to the plugin.
<br /><br />
<form method="post" action="options.php">
    <?php settings_fields( 'gpb_options' ); ?>
    <table class="form-table">
        <tr valign="top">
            <th scope="row">
                API Key<br />
                <small>via <a href="http://code.google.com/apis/console/">Google API Console</a></small>
            </th>
            <td><input type="text" name="gpb_api_key" value="<?php echo get_option('gpb_api_key'); ?>" /></td>
        </tr>

        <tr valign="top">
            <th scope="row">
                Profile/Page IDs and Authors<br />
                <small>21 digit number in your Profile/Page URL.</small>
            </th>

            <td>
                <?php
                $authors = get_option('gpb_profile_author', array());
                $profiles = get_option('gpb_profile_id', array());
                for ($x=0;$x < count($profiles);$x++) {
                ?>
                <div><input class='profile-id' name='gpb_profile_id[]' size=20 type='text' value="<?php echo $profiles[$x]; ?>""  placeholder='Enter another Profile ID'" />

                <?php wp_dropdown_users(array('name' => 'gpb_profile_author[]', 'who' => 'authors', 'selected' => $authors[$x], 'include_selected' => true, 'class' => 'wp-authors')); ?>
                </div>
                <?php } ?>
                <input class='profile-id' name='gpb_profile_id[]' size=20 type='text' value=""  placeholder='Enter a Profile ID' />
                <?php wp_dropdown_users(array('name' => 'gpb_profile_author[]', 'who' => 'authors', 'selected' => '', 'include_selected' => true, 'class' => 'wp-authors')); ?>
                <br />
                <input class='profile-id' name='gpb_profile_id[]' size=20 type='text' value=""  placeholder='Enter a Profile ID' />
                <?php wp_dropdown_users(array('name' => 'gpb_profile_author[]', 'who' => 'authors', 'selected' => '', 'include_selected' => true, 'class' => 'wp-authors')); ?>
                <br />
                <input class='profile-id' name='gpb_profile_id[]' size=20 type='text' value=""  placeholder='Enter a Profile ID' />
                <?php wp_dropdown_users(array('name' => 'gpb_profile_author[]', 'who' => 'authors', 'selected' => '', 'include_selected' => true, 'class' => 'wp-authors')); ?>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row">
                Post History Depth<br />
                <small>Number of posts to keep updated</small>
            </th>
            <td>
                <select name='gpb_post_limit'>
                    <option <?php echo get_option('gpb_post_limit') == '10' ? 'selected' : ''?>>10</option>
                    <option <?php echo get_option('gpb_post_limit') == '20' ? 'selected' : ''?>>20</option>
                    <option <?php echo get_option('gpb_post_limit') == '30' ? 'selected' : ''?>>30</option>
                    <option <?php echo get_option('gpb_post_limit') == '40' ? 'selected' : ''?>>40</option>
                    <option <?php echo get_option('gpb_post_limit') == '50' ? 'selected' : ''?>>50</option>
                    <option <?php echo get_option('gpb_post_limit') == '100' ? 'selected' : ''?>>100</option>
                    <option <?php echo get_option('gpb_post_limit') == '200' ? 'selected' : ''?>>200</option>
                    <option <?php echo get_option('gpb_post_limit') == '500' ? 'selected' : ''?>>500</option>
                    <option <?php echo get_option('gpb_post_limit') == '1000' ? 'selected' : ''?>>1000</option>

                </select>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row">
                Post Overwrite<br />
                <small>Update previously imported posts</small>
            </th>
            <td>
                <input type='checkbox' name='gpb_post_overwrite' value='1' <?php echo get_option('gpb_post_overwrite', true) ? 'checked' : '' ?>/>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row">
                Ignore Canonical<br />
                <small>Do not add canonical ref to Google+ posts</small>
            </th>
            <td>
                <input type='checkbox' name='gpb_ignore_canonical' value='1' <?php echo get_option('gpb_ignore_canonical', false) ? 'checked' : '' ?> />
            </td>
        </tr>

        <tr valign="top">
            <th scope="row">
                Import Trashed<br />
                <small>Import posts already trashed</small>
            </th>
            <td>
                <input type='checkbox' name='gpb_import_trashed' value='1' <?php echo get_option('gpb_import_trashed', true) ? 'checked' : '' ?> />
            </td>
        </tr>


        <tr valign="top">
            <th scope="row">
                Display Google+ Link<br />
                <small>Link back to Google+ post</small>
            </th>
            <td>
                <input type='checkbox' name='gpb_display_linkback' value='1' <?php echo get_option('gpb_display_linkback', true) ? 'checked' : '' ?> />
            </td>
        </tr>

        <tr valign="top">
            <th scope="row">
                Display Google+ Reshares<br />
                <small>Show reshare count</small>
            </th>
            <td>
                <input type='checkbox' name='gpb_display_reshares' value='1' <?php echo get_option('gpb_display_reshares', true) ? 'checked' : '' ?> />
            </td>
        </tr>

        <tr valign="top">
            <th scope="row">
                Display Photo/Video On Top<br />
                <small>If unchecked, will be at bottom</small>
            </th>
            <td>
                <input type='checkbox' name='gpb_photo_top' value='1' <?php echo get_option('gpb_photo_top', false) ? 'checked' : '' ?> />
            </td>
        </tr>

        <tr valign="top">
            <th scope="row">
                Display Album Link<br />
                <small>Link back to G+ Photo Album</small>
            </th>
            <td>
                <input type='checkbox' name='gpb_album_linkback' value='1' <?php echo get_option('gpb_album_linkback', false) ? 'checked' : '' ?> />
            </td>
        </tr>
        <tr valign="top">
            <th scope="row">
                Download Images for Feature<br />
                <small>Downloads the first image and sets as post thumbnail</small>
            </th>
            <td>
                <input type='checkbox' name='gpb_download_and_feature' value='1' <?php echo get_option('gpb_download_and_feature', false) ? 'checked' : '' ?> />
            </td>
        </tr>

        <tr valign="top">
            <th scope="row">
                Image Resolution<br />
                <small>Maximum resolution for main image</small>
            </th>
            <td>
                <input type='text' name='gpb_image_resolution' value='<?php echo get_option('gpb_image_resolution') ?>' />
            </td>
        </tr>

        <tr valign="top">
            <th scope="row">
                Import Tag<br />
                <small>Only import posts with this hashtag</small>
            </th>
            <td>
                <input type='text' name='gpb_import_tag' value='<?php echo get_option('gpb_import_tag') ?>' />
            </td>
        </tr>

        <tr valign="top">
            <th scope="row">
                Ignore Tag<br />
                <small>DO NOT import posts with this hashtag</small>
            </th>
            <td>
                <input type='text' name='gpb_ignore_tag' value='<?php echo get_option('gpb_ignore_tag') ?>' />
            </td>
        </tr>

        <tr valign="top">
            <th scope="row">
                Post Status<br />
                <small>Default created post status</small>
            </th>
            <td>
                <select name='gpb_post_status'>
                    <option <?php echo get_option('gpb_post_status') == 'Publish' ? 'selected' : ''?>>Publish</option>
                    <option <?php echo get_option('gpb_post_status') == 'Pending' ? 'selected' : ''?>>Pending</option>
                    <option <?php echo get_option('gpb_post_status') == 'Future' ? 'selected' : ''?>>Future</option>
                    <option <?php echo get_option('gpb_post_status') == 'Private' ? 'selected' : ''?>>Private</option>
                    <option <?php echo get_option('gpb_post_status') == 'Draft' ? 'selected' : ''?>>Draft</option>
                </select>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row">
                Post Categories<br />
                <small>WordPress categories to assign posts to</small>
            </th>
            <td>
                <select name='gpb_post_categories[]' multiple>
                <?php
                foreach (get_categories(array('hide_empty' => 0)) as $category)
                {
                ?>
                    <option value='<?php echo $category->cat_ID?>' <?php echo in_array($category->cat_ID,get_option('gpb_post_categories',array())) ? 'selected' : ''?>><?php echo $category->name?></option>
                <?php
                }
                ?>
                </select>

            </td>
        </tr>

        <tr valign="top">
            <th scope="row">
                Post Tags<br />
                <small>Comma separated</small>
            </th>
            <td>
                <input type='text' name='gpb_post_tags' value='<?php echo get_option('gpb_post_tags') ?>' />
            </td>
        </tr>


    </table>

    <?php submit_button(); ?>
    Import posts on options update: <input type='checkbox' name='gpb_run_now' value='1' />

</form>
</div>
<div class="wrap" style='margin-top:20px;'>
    <h3>Activity Log (<?php echo get_option('gpb_is_running', false) ? 'Running Now' : $next_run; ?>)</h3>
    <?php foreach (array_reverse(get_option('gpb_activity_log', array())) as $log) { ?>
    <p><?php echo $log ?></p>
    <?php } ?>
</div>
<?php } ?>

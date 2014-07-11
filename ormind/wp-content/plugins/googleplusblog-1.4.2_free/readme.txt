# Google+Blog for WordPress
Daniel Treadwell [daniel@minimali.se]
http://www.minimali.se/

*About*

Google+Blog for WordPress allows you to easily (and automatically) import your public Google+ posts, as well as their comments into your blog. Posts will include images and video, as well as links to articles you may have shared.

On import the plugin will first try to make a heading from the first line of your post, so please keep this in mind when formatting your posts on Google+.

If a post contains a hashtag they will be automatically added as tags to the post in WordPress.

Images, videos and articles are linked to and embedded (where possible) into your posts.  If your post contains an image album, it will display a thumbnail for all of the images except the feature image.

Comments are imported (and updated) as far back as your post history setting allows.

Whilst I do my best to keep this plugin both easy to use and bug free, I cannot be held responsible for any problems you may have because of its use.  In saying that, if you do have any problems just send me a message on Google+ or via email and I will do my best to help you.

*Initial setup*

To use this plugin all that you need is a WordPress (wordpress.org) install that allows you to load plugins, as well as a Google+ (plus.google.com) account and API Key (code.google.com/apis/console/).  The first two are self explanatory, but the API Key may not be.  It is required to fetch your public posts from Google and is done by following these steps.

1. Go to http://cloud.google.com/console
2. Create new project (if you have not been here before)
3. Switch on the Google+ API in the 'API & auth' -> 'APIs' menu
4. Under the 'Credentials' menu, create a 'Public API Access' Server key (Simply click create when the box comes up, you do not need to specify any IP Addresses)
5. You should now be able to see your API Key.

*Settings*

To get started you must edit the Google+Blog options under the Settings menu via WordPress.  Please fill in the following values.

_API Key_
As mentioned above, this gives you access to your Google+ feed.

_Profile/Page IDs and Authors_
This is your unique identifier on the Google+ network.  In Google+ click on your name and it should direct you to a URL that looks like this: https://plus.google.com/u/0/103697821787469756035/posts?hl=en .  Your Profile ID is the 21 digit number found in the URL (Mine is 103697821787469756035).

_Post History_
This setting determines how far back to look through your post history with each update.  The higher the number, the more posts that will be imported (or updated).

_Post Overwrite_
If enabled (by default), this will update your existing imported Google+ posts with whatever is pulled from the Google+ feed. If you make any changes at all to these posts, they will be overwritten in the next iteration.  Comments are updated regardless of this setting.

_Ignore Canonical_
Does not add a canonical entry to point to the relevant G+ post.  This is disabled by default, and a link will be added.

_Import Trashed_
Reimport a post even if it has previously been added to the trash.

_Display Google+ Link_
Adds a backlink to the bottom of each post directing to the Google+ post.

_Display Google+ Reshares_
Shows the number of reshares a post has received.

_Display Photo/Video on Top_
Adds the photo or video above the text content of the post.

_Display Google+ Album Link_
Shows link to the Google+ album when there is a photo attachment.

_Import tag_
Only import posts that have this hashtag attached to them (# not required)

_Exclude tag_
Do not import posts containing this hashtag.

_Post Status_
This is the default status for an imported post.  Most people will be happy with publish, but if you wish to use another simply choose between pending, future, private and draft.

_Post Categories_
Default categories can be chosen by selecting them from the list.  Each imported post will appear to be in all the categories chosen.

_Post Tags_
A comma separated list of tags you would like applied to the post.


# This-or-That
* Contributors: andrex84, thelonecabbage
- Tags: Rating, Ranking, Compare, Facemash, ELO, Chess Algorithm, Vote, Voting, Hot or Not, Research, Match, Pictures, Product Compare, Social, Collaborate, Andre Boekhorst
- Requires at least: 3.7.1
- Tested up to: 4.8
- Stable tag: 1.0.4
- License: GPLv2 or later
- License URI: http://www.gnu.org/licenses/gpl-2.0.html


Let your visitors vote between posts and images to create honest rankings. Uses the ELO Algorithm and works with your site's existing content.

## Description

An update of the fantastic [This-or-That](https://wordpress.org/plugins/this-or-that/) plugin by Andre Boekhorst. The plugin had, unfortunately, been abandoned, or at least not updated.  These are my hacks to bring it back to use.

Inspired by Facemash, Facebook's predecessor, this plugin sets up two items next to each other and lets visitors choose which one they favor. Each 'vote' adjusts the rating of both items. You can then easily create lists with your visitors favorite items. [Updated Fix for WordPress 4.1]


See this [plugin in action](http://andreboekhorst.nl/wordpress/this-or-that-plugin/example-moviemash/ "Moviemash").

## Works with content already on your site!
Start by using what you've already got. The This-or-That plugin works with any post type already on your website like your blog posts, photos, recipies, or WooCommerce products.

## Item Manager
If you want to keep your This-or-That items seperate thats no problem. This plugin creates a seperate section in your Admin where you can create and manage your 'This-or-That' items. You can even sort them by ranking, wins, and losses.

## Easy Implementation
If you've added some This-or-That items, the only thing left to do is add the shortcode `[thisorthat]` to a page. Displaying the ranking lists is just as easy: `[thisorthat_ranking]`. Please see the FAQ for some extra options like showing post-types or categories.

## Elegant AJAX Interface
The plugin comes with an elegant, user-friendly voting interface. Items will automatically get updated without a page-refresh and you can even vote using your *left* and *right* keys on your keyboard.

## Create Lists per Category
Show lists from your different categories or custom taxonomies.

## Uses the ELO Rating system
This-or-That uses the [ELO Rating System](http://en.wikipedia.org/wiki/Elo_rating_system "ELO Rating System"), created to rank chess players but now used in a variety of fields.

## Be Creative!
There are plenty of ways to use this plugin; get some insight in what your visitors like or let them engage with your sites content. Some stuff you might want to use this plugin with:

* Recipies
- Woocommerce Products
- Holiday Images
- Simpsons Episodes
- Design Proposals
- Artworks
- Books
- Etc...

Read more on the [plugin website](http://andreboekhorst.nl/wordpress/this-or-that-plugin/ "Andr&eactute Boekhorst").

## Installation

1. Unzip and upload the "this-or-that" folder to the "/wp-content/plugins/" directory.
2. Activate the plugin through the "Plugins" menu in WordPress.
3. Add items to the new This-or-That post type in your admin screen. Don't forget the featured image!
4. Add the shortcode `[thisorthat]` to the page where you want people to compare the different items
5. Add the shortcode `[thisorthat_ranking]` to the page where you want to view the ranking list.

## Frequently Asked Questions

1. How do I get started:
    * You can use the plugin anywhere in your websites content. First make sure you have some content to compare by adding items to the *This-or-That* post-type in the Admin. Make sure to add some featured images! When this has been done, you can add the shortcode `[thisorthat]` to any of your posts or pages.
2. How do I display the ranking list
    * This van be done by using the shortcode `[thisorthat_ranking]`.
1. How to use my own Posts types?
    * Add a 'posttype' variable to your shortcode. Example: `[thisorthat posttype="product"]`.
1.How can I use a certain category?
    * Add a 'category' variable to your shortcode. Example: `[thisorthat category="holiday_pics"]`.
    - By deafult, This-or-That will use the categories set under the *This-or-That* amin item. If you use a different post-type, it will generally have a different taxonomy. Therefore, you have to set which taxonomy you want to use.For example, if you want compare a category from WooCommerce you should use `[thisorthat taxonomy="product_cat" category="Sweaters"]`

## How do I customize the 'voting' appearance?
You can control which elements you want to show through a couple of *true* or *false* variables. These are:
* show_image : Shows the posts featured image (default: *true*)
- show_title : Shows the post title (default: *true*)
- show_excerpt : Shows the exceprts of the posts content (deafult: *false*)
- show_score : Shows the items ranking (default: *false*)

## How do I change the thumbnail size?
You can use the *thumb_size* variable. Example `[thisorthat thumb_size="medium"]`. It uses any thumbnail size you use in your website. WordPress by default supports: *thumbnail*, *medium*, *large*, or *full*.

## Screenshots
1. The screenshot description corresponds to screenshot-1.(png|jpg|jpeg|gif).
2. The screenshot description corresponds to screenshot-2.(png|jpg|jpeg|gif).
3. The screenshot description corresponds to screenshot-3.(png|jpg|jpeg|gif).

## Changelog
## 1.0
* Initial release.

## 1.0.1
* Readme.txt changes.

## 1.0.2
* Readme.txt changes.

## 1.0.3
- Fixed several Javascript bugs. With help from gdibble.

## 1.1.0 (2017-10-16)
- Fixed Multisite handling
- Added CSS for rankings
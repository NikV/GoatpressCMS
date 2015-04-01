<?php
/**
 * Goatpress core upgrade functionality.
 *
 * @package Goatpress
 * @subpackage Administration
 * @since 2.7.0
 */

/**
 * Stores files to be deleted.
 *
 * @since 2.7.0
 * @global array $_old_files
 * @var array
 * @name $_old_files
 */
global $_old_files;

$_old_files = array(
// 2.0
'gp-admin/import-b2.php',
'gp-admin/import-blogger.php',
'gp-admin/import-greymatter.php',
'gp-admin/import-livejournal.php',
'gp-admin/import-mt.php',
'gp-admin/import-rss.php',
'gp-admin/import-textpattern.php',
'gp-admin/quicktags.js',
'gp-images/fade-butt.png',
'gp-images/get-firefox.png',
'gp-images/header-shadow.png',
'gp-images/smilies',
'gp-images/gp-small.png',
'gp-images/gpminilogo.png',
'gp.php',
// 2.0.8
'gp-includes/js/tinymce/plugins/inlinepopups/readme.txt',
// 2.1
'gp-admin/edit-form-ajax-cat.php',
'gp-admin/execute-pings.php',
'gp-admin/inline-uploading.php',
'gp-admin/link-categories.php',
'gp-admin/list-manipulation.js',
'gp-admin/list-manipulation.php',
'gp-includes/comment-functions.php',
'gp-includes/feed-functions.php',
'gp-includes/functions-compat.php',
'gp-includes/functions-formatting.php',
'gp-includes/functions-post.php',
'gp-includes/js/dbx-key.js',
'gp-includes/js/tinymce/plugins/autosave/langs/cs.js',
'gp-includes/js/tinymce/plugins/autosave/langs/sv.js',
'gp-includes/links.php',
'gp-includes/pluggable-functions.php',
'gp-includes/template-functions-author.php',
'gp-includes/template-functions-category.php',
'gp-includes/template-functions-general.php',
'gp-includes/template-functions-links.php',
'gp-includes/template-functions-post.php',
'gp-includes/gp-l10n.php',
// 2.2
'gp-admin/cat-js.php',
'gp-admin/import/b2.php',
'gp-includes/js/autosave-js.php',
'gp-includes/js/list-manipulation-js.php',
'gp-includes/js/gp-ajax-js.php',
// 2.3
'gp-admin/admin-db.php',
'gp-admin/cat.js',
'gp-admin/categories.js',
'gp-admin/custom-fields.js',
'gp-admin/dbx-admin-key.js',
'gp-admin/edit-comments.js',
'gp-admin/install-rtl.css',
'gp-admin/install.css',
'gp-admin/upgrade-schema.php',
'gp-admin/upload-functions.php',
'gp-admin/upload-rtl.css',
'gp-admin/upload.css',
'gp-admin/upload.js',
'gp-admin/users.js',
'gp-admin/widgets-rtl.css',
'gp-admin/widgets.css',
'gp-admin/xfn.js',
'gp-includes/js/tinymce/license.html',
// 2.5
'gp-admin/css/upload.css',
'gp-admin/images/box-bg-left.gif',
'gp-admin/images/box-bg-right.gif',
'gp-admin/images/box-bg.gif',
'gp-admin/images/box-butt-left.gif',
'gp-admin/images/box-butt-right.gif',
'gp-admin/images/box-butt.gif',
'gp-admin/images/box-head-left.gif',
'gp-admin/images/box-head-right.gif',
'gp-admin/images/box-head.gif',
'gp-admin/images/heading-bg.gif',
'gp-admin/images/login-bkg-bottom.gif',
'gp-admin/images/login-bkg-tile.gif',
'gp-admin/images/notice.gif',
'gp-admin/images/toggle.gif',
'gp-admin/includes/upload.php',
'gp-admin/js/dbx-admin-key.js',
'gp-admin/js/link-cat.js',
'gp-admin/profile-update.php',
'gp-admin/templates.php',
'gp-includes/images/wlw/gpComments.png',
'gp-includes/images/wlw/gpIcon.png',
'gp-includes/images/wlw/gpWatermark.png',
'gp-includes/js/dbx.js',
'gp-includes/js/fat.js',
'gp-includes/js/list-manipulation.js',
'gp-includes/js/tinymce/langs/en.js',
'gp-includes/js/tinymce/plugins/autosave/editor_plugin_src.js',
'gp-includes/js/tinymce/plugins/autosave/langs',
'gp-includes/js/tinymce/plugins/directionality/images',
'gp-includes/js/tinymce/plugins/directionality/langs',
'gp-includes/js/tinymce/plugins/inlinepopups/css',
'gp-includes/js/tinymce/plugins/inlinepopups/images',
'gp-includes/js/tinymce/plugins/inlinepopups/jscripts',
'gp-includes/js/tinymce/plugins/paste/images',
'gp-includes/js/tinymce/plugins/paste/jscripts',
'gp-includes/js/tinymce/plugins/paste/langs',
'gp-includes/js/tinymce/plugins/spellchecker/classes/HttpClient.class.php',
'gp-includes/js/tinymce/plugins/spellchecker/classes/TinyGoogleSpell.class.php',
'gp-includes/js/tinymce/plugins/spellchecker/classes/TinyPspell.class.php',
'gp-includes/js/tinymce/plugins/spellchecker/classes/TinyPspellShell.class.php',
'gp-includes/js/tinymce/plugins/spellchecker/css/spellchecker.css',
'gp-includes/js/tinymce/plugins/spellchecker/images',
'gp-includes/js/tinymce/plugins/spellchecker/langs',
'gp-includes/js/tinymce/plugins/spellchecker/tinyspell.php',
'gp-includes/js/tinymce/plugins/Goatpress/images',
'gp-includes/js/tinymce/plugins/Goatpress/langs',
'gp-includes/js/tinymce/plugins/Goatpress/Goatpress.css',
'gp-includes/js/tinymce/plugins/gphelp',
'gp-includes/js/tinymce/themes/advanced/css',
'gp-includes/js/tinymce/themes/advanced/images',
'gp-includes/js/tinymce/themes/advanced/jscripts',
'gp-includes/js/tinymce/themes/advanced/langs',
// 2.5.1
'gp-includes/js/tinymce/tiny_mce_gzip.php',
// 2.6
'gp-admin/bookmarklet.php',
'gp-includes/js/jquery/jquery.dimensions.min.js',
'gp-includes/js/tinymce/plugins/Goatpress/popups.css',
'gp-includes/js/gp-ajax.js',
// 2.7
'gp-admin/css/press-this-ie-rtl.css',
'gp-admin/css/press-this-ie.css',
'gp-admin/css/upload-rtl.css',
'gp-admin/edit-form.php',
'gp-admin/images/comment-pill.gif',
'gp-admin/images/comment-stalk-classic.gif',
'gp-admin/images/comment-stalk-fresh.gif',
'gp-admin/images/comment-stalk-rtl.gif',
'gp-admin/images/del.png',
'gp-admin/images/gear.png',
'gp-admin/images/media-button-gallery.gif',
'gp-admin/images/media-buttons.gif',
'gp-admin/images/postbox-bg.gif',
'gp-admin/images/tab.png',
'gp-admin/images/tail.gif',
'gp-admin/js/forms.js',
'gp-admin/js/upload.js',
'gp-admin/link-import.php',
'gp-includes/images/audio.png',
'gp-includes/images/css.png',
'gp-includes/images/default.png',
'gp-includes/images/doc.png',
'gp-includes/images/exe.png',
'gp-includes/images/html.png',
'gp-includes/images/js.png',
'gp-includes/images/pdf.png',
'gp-includes/images/swf.png',
'gp-includes/images/tar.png',
'gp-includes/images/text.png',
'gp-includes/images/video.png',
'gp-includes/images/zip.png',
'gp-includes/js/tinymce/tiny_mce_config.php',
'gp-includes/js/tinymce/tiny_mce_ext.js',
// 2.8
'gp-admin/js/users.js',
'gp-includes/js/swfupload/plugins/swfupload.documentready.js',
'gp-includes/js/swfupload/plugins/swfupload.graceful_degradation.js',
'gp-includes/js/swfupload/swfupload_f9.swf',
'gp-includes/js/tinymce/plugins/autosave',
'gp-includes/js/tinymce/plugins/paste/css',
'gp-includes/js/tinymce/utils/mclayer.js',
'gp-includes/js/tinymce/Goatpress.css',
// 2.8.5
'gp-admin/import/btt.php',
'gp-admin/import/jkw.php',
// 2.9
'gp-admin/js/page.dev.js',
'gp-admin/js/page.js',
'gp-admin/js/set-post-thumbnail-handler.dev.js',
'gp-admin/js/set-post-thumbnail-handler.js',
'gp-admin/js/slug.dev.js',
'gp-admin/js/slug.js',
'gp-includes/gettext.php',
'gp-includes/js/tinymce/plugins/Goatpress/js',
'gp-includes/streams.php',
// MU
'README.txt',
'htaccess.dist',
'index-install.php',
'gp-admin/css/mu-rtl.css',
'gp-admin/css/mu.css',
'gp-admin/images/site-admin.png',
'gp-admin/includes/mu.php',
'gp-admin/gpmu-admin.php',
'gp-admin/gpmu-blogs.php',
'gp-admin/gpmu-edit.php',
'gp-admin/gpmu-options.php',
'gp-admin/gpmu-themes.php',
'gp-admin/gpmu-upgrade-site.php',
'gp-admin/gpmu-users.php',
'gp-includes/images/Goatpress-mu.png',
'gp-includes/gpmu-default-filters.php',
'gp-includes/gpmu-functions.php',
'gpmu-settings.php',
// 3.0
'gp-admin/categories.php',
'gp-admin/edit-category-form.php',
'gp-admin/edit-page-form.php',
'gp-admin/edit-pages.php',
'gp-admin/images/admin-header-footer.png',
'gp-admin/images/browse-happy.gif',
'gp-admin/images/ico-add.png',
'gp-admin/images/ico-close.png',
'gp-admin/images/ico-edit.png',
'gp-admin/images/ico-viegpage.png',
'gp-admin/images/fav-top.png',
'gp-admin/images/screen-options-left.gif',
'gp-admin/images/gp-logo-vs.gif',
'gp-admin/images/gp-logo.gif',
'gp-admin/import',
'gp-admin/js/gp-gears.dev.js',
'gp-admin/js/gp-gears.js',
'gp-admin/options-misc.php',
'gp-admin/page-new.php',
'gp-admin/page.php',
'gp-admin/rtl.css',
'gp-admin/rtl.dev.css',
'gp-admin/update-links.php',
'gp-admin/gp-admin.css',
'gp-admin/gp-admin.dev.css',
'gp-includes/js/codepress',
'gp-includes/js/codepress/engines/khtml.js',
'gp-includes/js/codepress/engines/older.js',
'gp-includes/js/jquery/autocomplete.dev.js',
'gp-includes/js/jquery/autocomplete.js',
'gp-includes/js/jquery/interface.js',
'gp-includes/js/scriptaculous/prototype.js',
'gp-includes/js/tinymce/gp-tinymce.js',
// 3.1
'gp-admin/edit-attachment-rows.php',
'gp-admin/edit-link-categories.php',
'gp-admin/edit-link-category-form.php',
'gp-admin/edit-post-rows.php',
'gp-admin/images/button-grad-active-vs.png',
'gp-admin/images/button-grad-vs.png',
'gp-admin/images/fav-arrow-vs-rtl.gif',
'gp-admin/images/fav-arrow-vs.gif',
'gp-admin/images/fav-top-vs.gif',
'gp-admin/images/list-vs.png',
'gp-admin/images/screen-options-right-up.gif',
'gp-admin/images/screen-options-right.gif',
'gp-admin/images/visit-site-button-grad-vs.gif',
'gp-admin/images/visit-site-button-grad.gif',
'gp-admin/link-category.php',
'gp-admin/sidebar.php',
'gp-includes/classes.php',
'gp-includes/js/tinymce/blank.htm',
'gp-includes/js/tinymce/plugins/media/css/content.css',
'gp-includes/js/tinymce/plugins/media/img',
'gp-includes/js/tinymce/plugins/safari',
// 3.2
'gp-admin/images/logo-login.gif',
'gp-admin/images/star.gif',
'gp-admin/js/list-table.dev.js',
'gp-admin/js/list-table.js',
'gp-includes/default-embeds.php',
'gp-includes/js/tinymce/plugins/Goatpress/img/help.gif',
'gp-includes/js/tinymce/plugins/Goatpress/img/more.gif',
'gp-includes/js/tinymce/plugins/Goatpress/img/toolbars.gif',
'gp-includes/js/tinymce/themes/advanced/img/fm.gif',
'gp-includes/js/tinymce/themes/advanced/img/sflogo.png',
// 3.3
'gp-admin/css/colors-classic-rtl.css',
'gp-admin/css/colors-classic-rtl.dev.css',
'gp-admin/css/colors-fresh-rtl.css',
'gp-admin/css/colors-fresh-rtl.dev.css',
'gp-admin/css/dashboard-rtl.dev.css',
'gp-admin/css/dashboard.dev.css',
'gp-admin/css/global-rtl.css',
'gp-admin/css/global-rtl.dev.css',
'gp-admin/css/global.css',
'gp-admin/css/global.dev.css',
'gp-admin/css/install-rtl.dev.css',
'gp-admin/css/login-rtl.dev.css',
'gp-admin/css/login.dev.css',
'gp-admin/css/ms.css',
'gp-admin/css/ms.dev.css',
'gp-admin/css/nav-menu-rtl.css',
'gp-admin/css/nav-menu-rtl.dev.css',
'gp-admin/css/nav-menu.css',
'gp-admin/css/nav-menu.dev.css',
'gp-admin/css/plugin-install-rtl.css',
'gp-admin/css/plugin-install-rtl.dev.css',
'gp-admin/css/plugin-install.css',
'gp-admin/css/plugin-install.dev.css',
'gp-admin/css/press-this-rtl.dev.css',
'gp-admin/css/press-this.dev.css',
'gp-admin/css/theme-editor-rtl.css',
'gp-admin/css/theme-editor-rtl.dev.css',
'gp-admin/css/theme-editor.css',
'gp-admin/css/theme-editor.dev.css',
'gp-admin/css/theme-install-rtl.css',
'gp-admin/css/theme-install-rtl.dev.css',
'gp-admin/css/theme-install.css',
'gp-admin/css/theme-install.dev.css',
'gp-admin/css/widgets-rtl.dev.css',
'gp-admin/css/widgets.dev.css',
'gp-admin/includes/internal-linking.php',
'gp-includes/images/admin-bar-sprite-rtl.png',
'gp-includes/js/jquery/ui.button.js',
'gp-includes/js/jquery/ui.core.js',
'gp-includes/js/jquery/ui.dialog.js',
'gp-includes/js/jquery/ui.draggable.js',
'gp-includes/js/jquery/ui.droppable.js',
'gp-includes/js/jquery/ui.mouse.js',
'gp-includes/js/jquery/ui.position.js',
'gp-includes/js/jquery/ui.resizable.js',
'gp-includes/js/jquery/ui.selectable.js',
'gp-includes/js/jquery/ui.sortable.js',
'gp-includes/js/jquery/ui.tabs.js',
'gp-includes/js/jquery/ui.widget.js',
'gp-includes/js/l10n.dev.js',
'gp-includes/js/l10n.js',
'gp-includes/js/tinymce/plugins/gplink/css',
'gp-includes/js/tinymce/plugins/gplink/img',
'gp-includes/js/tinymce/plugins/gplink/js',
'gp-includes/js/tinymce/themes/advanced/img/gpicons.png',
'gp-includes/js/tinymce/themes/advanced/skins/gp_theme/img/butt2.png',
'gp-includes/js/tinymce/themes/advanced/skins/gp_theme/img/button_bg.png',
'gp-includes/js/tinymce/themes/advanced/skins/gp_theme/img/down_arrow.gif',
'gp-includes/js/tinymce/themes/advanced/skins/gp_theme/img/fade-butt.png',
'gp-includes/js/tinymce/themes/advanced/skins/gp_theme/img/separator.gif',
// Don't delete, yet: 'gp-rss.php',
// Don't delete, yet: 'gp-rdf.php',
// Don't delete, yet: 'gp-rss2.php',
// Don't delete, yet: 'gp-commentsrss2.php',
// Don't delete, yet: 'gp-atom.php',
// Don't delete, yet: 'gp-feed.php',
// 3.4
'gp-admin/images/gray-star.png',
'gp-admin/images/logo-login.png',
'gp-admin/images/star.png',
'gp-admin/index-extra.php',
'gp-admin/network/index-extra.php',
'gp-admin/user/index-extra.php',
'gp-admin/images/screenshots/admin-flyouts.png',
'gp-admin/images/screenshots/coediting.png',
'gp-admin/images/screenshots/drag-and-drop.png',
'gp-admin/images/screenshots/help-screen.png',
'gp-admin/images/screenshots/media-icon.png',
'gp-admin/images/screenshots/new-feature-pointer.png',
'gp-admin/images/screenshots/welcome-screen.png',
'gp-includes/css/editor-buttons.css',
'gp-includes/css/editor-buttons.dev.css',
'gp-includes/js/tinymce/plugins/paste/blank.htm',
'gp-includes/js/tinymce/plugins/Goatpress/css',
'gp-includes/js/tinymce/plugins/Goatpress/editor_plugin.dev.js',
'gp-includes/js/tinymce/plugins/Goatpress/img/embedded.png',
'gp-includes/js/tinymce/plugins/Goatpress/img/more_bug.gif',
'gp-includes/js/tinymce/plugins/Goatpress/img/page_bug.gif',
'gp-includes/js/tinymce/plugins/gpdialogs/editor_plugin.dev.js',
'gp-includes/js/tinymce/plugins/gpeditimage/css/editimage-rtl.css',
'gp-includes/js/tinymce/plugins/gpeditimage/editor_plugin.dev.js',
'gp-includes/js/tinymce/plugins/gpfullscreen/editor_plugin.dev.js',
'gp-includes/js/tinymce/plugins/gpgallery/editor_plugin.dev.js',
'gp-includes/js/tinymce/plugins/gpgallery/img/gallery.png',
'gp-includes/js/tinymce/plugins/gplink/editor_plugin.dev.js',
// Don't delete, yet: 'gp-pass.php',
// Don't delete, yet: 'gp-register.php',
// 3.5
'gp-admin/gears-manifest.php',
'gp-admin/includes/manifest.php',
'gp-admin/images/archive-link.png',
'gp-admin/images/blue-grad.png',
'gp-admin/images/button-grad-active.png',
'gp-admin/images/button-grad.png',
'gp-admin/images/ed-bg-vs.gif',
'gp-admin/images/ed-bg.gif',
'gp-admin/images/fade-butt.png',
'gp-admin/images/fav-arrow-rtl.gif',
'gp-admin/images/fav-arrow.gif',
'gp-admin/images/fav-vs.png',
'gp-admin/images/fav.png',
'gp-admin/images/gray-grad.png',
'gp-admin/images/loading-publish.gif',
'gp-admin/images/logo-ghost.png',
'gp-admin/images/logo.gif',
'gp-admin/images/menu-arrow-frame-rtl.png',
'gp-admin/images/menu-arrow-frame.png',
'gp-admin/images/menu-arrows.gif',
'gp-admin/images/menu-bits-rtl-vs.gif',
'gp-admin/images/menu-bits-rtl.gif',
'gp-admin/images/menu-bits-vs.gif',
'gp-admin/images/menu-bits.gif',
'gp-admin/images/menu-dark-rtl-vs.gif',
'gp-admin/images/menu-dark-rtl.gif',
'gp-admin/images/menu-dark-vs.gif',
'gp-admin/images/menu-dark.gif',
'gp-admin/images/required.gif',
'gp-admin/images/screen-options-toggle-vs.gif',
'gp-admin/images/screen-options-toggle.gif',
'gp-admin/images/toggle-arrow-rtl.gif',
'gp-admin/images/toggle-arrow.gif',
'gp-admin/images/upload-classic.png',
'gp-admin/images/upload-fresh.png',
'gp-admin/images/white-grad-active.png',
'gp-admin/images/white-grad.png',
'gp-admin/images/widgets-arrow-vs.gif',
'gp-admin/images/widgets-arrow.gif',
'gp-admin/images/gpspin_dark.gif',
'gp-includes/images/upload.png',
'gp-includes/js/prototype.js',
'gp-includes/js/scriptaculous',
'gp-admin/css/gp-admin-rtl.dev.css',
'gp-admin/css/gp-admin.dev.css',
'gp-admin/css/media-rtl.dev.css',
'gp-admin/css/media.dev.css',
'gp-admin/css/colors-classic.dev.css',
'gp-admin/css/customize-controls-rtl.dev.css',
'gp-admin/css/customize-controls.dev.css',
'gp-admin/css/ie-rtl.dev.css',
'gp-admin/css/ie.dev.css',
'gp-admin/css/install.dev.css',
'gp-admin/css/colors-fresh.dev.css',
'gp-includes/js/customize-base.dev.js',
'gp-includes/js/json2.dev.js',
'gp-includes/js/comment-reply.dev.js',
'gp-includes/js/customize-preview.dev.js',
'gp-includes/js/gplink.dev.js',
'gp-includes/js/tw-sack.dev.js',
'gp-includes/js/gp-list-revisions.dev.js',
'gp-includes/js/autosave.dev.js',
'gp-includes/js/admin-bar.dev.js',
'gp-includes/js/quicktags.dev.js',
'gp-includes/js/gp-ajax-response.dev.js',
'gp-includes/js/gp-pointer.dev.js',
'gp-includes/js/hoverIntent.dev.js',
'gp-includes/js/colorpicker.dev.js',
'gp-includes/js/gp-lists.dev.js',
'gp-includes/js/customize-loader.dev.js',
'gp-includes/js/jquery/jquery.table-hotkeys.dev.js',
'gp-includes/js/jquery/jquery.color.dev.js',
'gp-includes/js/jquery/jquery.color.js',
'gp-includes/js/jquery/jquery.hotkeys.dev.js',
'gp-includes/js/jquery/jquery.form.dev.js',
'gp-includes/js/jquery/suggest.dev.js',
'gp-admin/js/xfn.dev.js',
'gp-admin/js/set-post-thumbnail.dev.js',
'gp-admin/js/comment.dev.js',
'gp-admin/js/theme.dev.js',
'gp-admin/js/cat.dev.js',
'gp-admin/js/password-strength-meter.dev.js',
'gp-admin/js/user-profile.dev.js',
'gp-admin/js/theme-preview.dev.js',
'gp-admin/js/post.dev.js',
'gp-admin/js/media-upload.dev.js',
'gp-admin/js/word-count.dev.js',
'gp-admin/js/plugin-install.dev.js',
'gp-admin/js/edit-comments.dev.js',
'gp-admin/js/media-gallery.dev.js',
'gp-admin/js/custom-fields.dev.js',
'gp-admin/js/custom-background.dev.js',
'gp-admin/js/common.dev.js',
'gp-admin/js/inline-edit-tax.dev.js',
'gp-admin/js/gallery.dev.js',
'gp-admin/js/utils.dev.js',
'gp-admin/js/widgets.dev.js',
'gp-admin/js/gp-fullscreen.dev.js',
'gp-admin/js/nav-menu.dev.js',
'gp-admin/js/dashboard.dev.js',
'gp-admin/js/link.dev.js',
'gp-admin/js/user-suggest.dev.js',
'gp-admin/js/postbox.dev.js',
'gp-admin/js/tags.dev.js',
'gp-admin/js/image-edit.dev.js',
'gp-admin/js/media.dev.js',
'gp-admin/js/customize-controls.dev.js',
'gp-admin/js/inline-edit-post.dev.js',
'gp-admin/js/categories.dev.js',
'gp-admin/js/editor.dev.js',
'gp-includes/js/tinymce/plugins/gpeditimage/js/editimage.dev.js',
'gp-includes/js/tinymce/plugins/gpdialogs/js/popup.dev.js',
'gp-includes/js/tinymce/plugins/gpdialogs/js/gpdialog.dev.js',
'gp-includes/js/plupload/handlers.dev.js',
'gp-includes/js/plupload/gp-plupload.dev.js',
'gp-includes/js/swfupload/handlers.dev.js',
'gp-includes/js/jcrop/jquery.Jcrop.dev.js',
'gp-includes/js/jcrop/jquery.Jcrop.js',
'gp-includes/js/jcrop/jquery.Jcrop.css',
'gp-includes/js/imgareaselect/jquery.imgareaselect.dev.js',
'gp-includes/css/gp-pointer.dev.css',
'gp-includes/css/editor.dev.css',
'gp-includes/css/jquery-ui-dialog.dev.css',
'gp-includes/css/admin-bar-rtl.dev.css',
'gp-includes/css/admin-bar.dev.css',
'gp-includes/js/jquery/ui/jquery.effects.clip.min.js',
'gp-includes/js/jquery/ui/jquery.effects.scale.min.js',
'gp-includes/js/jquery/ui/jquery.effects.blind.min.js',
'gp-includes/js/jquery/ui/jquery.effects.core.min.js',
'gp-includes/js/jquery/ui/jquery.effects.shake.min.js',
'gp-includes/js/jquery/ui/jquery.effects.fade.min.js',
'gp-includes/js/jquery/ui/jquery.effects.explode.min.js',
'gp-includes/js/jquery/ui/jquery.effects.slide.min.js',
'gp-includes/js/jquery/ui/jquery.effects.drop.min.js',
'gp-includes/js/jquery/ui/jquery.effects.highlight.min.js',
'gp-includes/js/jquery/ui/jquery.effects.bounce.min.js',
'gp-includes/js/jquery/ui/jquery.effects.pulsate.min.js',
'gp-includes/js/jquery/ui/jquery.effects.transfer.min.js',
'gp-includes/js/jquery/ui/jquery.effects.fold.min.js',
'gp-admin/images/screenshots/captions-1.png',
'gp-admin/images/screenshots/captions-2.png',
'gp-admin/images/screenshots/flex-header-1.png',
'gp-admin/images/screenshots/flex-header-2.png',
'gp-admin/images/screenshots/flex-header-3.png',
'gp-admin/images/screenshots/flex-header-media-library.png',
'gp-admin/images/screenshots/theme-customizer.png',
'gp-admin/images/screenshots/twitter-embed-1.png',
'gp-admin/images/screenshots/twitter-embed-2.png',
'gp-admin/js/utils.js',
'gp-admin/options-privacy.php',
'gp-app.php',
'gp-includes/class-gp-atom-server.php',
'gp-includes/js/tinymce/themes/advanced/skins/gp_theme/ui.css',
// 3.5.2
'gp-includes/js/swfupload/swfupload-all.js',
// 3.6
'gp-admin/js/revisions-js.php',
'gp-admin/images/screenshots',
'gp-admin/js/categories.js',
'gp-admin/js/categories.min.js',
'gp-admin/js/custom-fields.js',
'gp-admin/js/custom-fields.min.js',
// 3.7
'gp-admin/js/cat.js',
'gp-admin/js/cat.min.js',
'gp-includes/js/tinymce/plugins/gpeditimage/js/editimage.min.js',
// 3.8
'gp-includes/js/tinymce/themes/advanced/skins/gp_theme/img/page_bug.gif',
'gp-includes/js/tinymce/themes/advanced/skins/gp_theme/img/more_bug.gif',
'gp-includes/js/thickbox/tb-close-2x.png',
'gp-includes/js/thickbox/tb-close.png',
'gp-includes/images/gpmini-blue-2x.png',
'gp-includes/images/gpmini-blue.png',
'gp-admin/css/colors-fresh.css',
'gp-admin/css/colors-classic.css',
'gp-admin/css/colors-fresh.min.css',
'gp-admin/css/colors-classic.min.css',
'gp-admin/js/about.min.js',
'gp-admin/js/about.js',
'gp-admin/images/arrows-dark-vs-2x.png',
'gp-admin/images/gp-logo-vs.png',
'gp-admin/images/arrows-dark-vs.png',
'gp-admin/images/gp-logo.png',
'gp-admin/images/arrows-pr.png',
'gp-admin/images/arrows-dark.png',
'gp-admin/images/press-this.png',
'gp-admin/images/press-this-2x.png',
'gp-admin/images/arrows-vs-2x.png',
'gp-admin/images/welcome-icons.png',
'gp-admin/images/gp-logo-2x.png',
'gp-admin/images/stars-rtl-2x.png',
'gp-admin/images/arrows-dark-2x.png',
'gp-admin/images/arrows-pr-2x.png',
'gp-admin/images/menu-shadow-rtl.png',
'gp-admin/images/arrows-vs.png',
'gp-admin/images/about-search-2x.png',
'gp-admin/images/bubble_bg-rtl-2x.gif',
'gp-admin/images/gp-badge-2x.png',
'gp-admin/images/Goatpress-logo-2x.png',
'gp-admin/images/bubble_bg-rtl.gif',
'gp-admin/images/gp-badge.png',
'gp-admin/images/menu-shadow.png',
'gp-admin/images/about-globe-2x.png',
'gp-admin/images/welcome-icons-2x.png',
'gp-admin/images/stars-rtl.png',
'gp-admin/images/gp-logo-vs-2x.png',
'gp-admin/images/about-updates-2x.png',
// 3.9
'gp-admin/css/colors.css',
'gp-admin/css/colors.min.css',
'gp-admin/css/colors-rtl.css',
'gp-admin/css/colors-rtl.min.css',
'gp-admin/css/media-rtl.min.css',
'gp-admin/css/media.min.css',
'gp-admin/css/farbtastic-rtl.min.css',
'gp-admin/images/lock-2x.png',
'gp-admin/images/lock.png',
'gp-admin/js/theme-preview.js',
'gp-admin/js/theme-install.min.js',
'gp-admin/js/theme-install.js',
'gp-admin/js/theme-preview.min.js',
'gp-includes/js/plupload/plupload.html4.js',
'gp-includes/js/plupload/plupload.html5.js',
'gp-includes/js/plupload/changelog.txt',
'gp-includes/js/plupload/plupload.silverlight.js',
'gp-includes/js/plupload/plupload.flash.js',
'gp-includes/js/plupload/plupload.js',
'gp-includes/js/tinymce/plugins/spellchecker',
'gp-includes/js/tinymce/plugins/inlinepopups',
'gp-includes/js/tinymce/plugins/media/js',
'gp-includes/js/tinymce/plugins/media/css',
'gp-includes/js/tinymce/plugins/Goatpress/img',
'gp-includes/js/tinymce/plugins/gpdialogs/js',
'gp-includes/js/tinymce/plugins/gpeditimage/img',
'gp-includes/js/tinymce/plugins/gpeditimage/js',
'gp-includes/js/tinymce/plugins/gpeditimage/css',
'gp-includes/js/tinymce/plugins/gpgallery/img',
'gp-includes/js/tinymce/plugins/gpfullscreen/css',
'gp-includes/js/tinymce/plugins/paste/js',
'gp-includes/js/tinymce/themes/advanced',
'gp-includes/js/tinymce/tiny_mce.js',
'gp-includes/js/tinymce/mark_loaded_src.js',
'gp-includes/js/tinymce/gp-tinymce-schema.js',
'gp-includes/js/tinymce/plugins/media/editor_plugin.js',
'gp-includes/js/tinymce/plugins/media/editor_plugin_src.js',
'gp-includes/js/tinymce/plugins/media/media.htm',
'gp-includes/js/tinymce/plugins/gpview/editor_plugin_src.js',
'gp-includes/js/tinymce/plugins/gpview/editor_plugin.js',
'gp-includes/js/tinymce/plugins/directionality/editor_plugin.js',
'gp-includes/js/tinymce/plugins/directionality/editor_plugin_src.js',
'gp-includes/js/tinymce/plugins/Goatpress/editor_plugin.js',
'gp-includes/js/tinymce/plugins/Goatpress/editor_plugin_src.js',
'gp-includes/js/tinymce/plugins/gpdialogs/editor_plugin_src.js',
'gp-includes/js/tinymce/plugins/gpdialogs/editor_plugin.js',
'gp-includes/js/tinymce/plugins/gpeditimage/editimage.html',
'gp-includes/js/tinymce/plugins/gpeditimage/editor_plugin.js',
'gp-includes/js/tinymce/plugins/gpeditimage/editor_plugin_src.js',
'gp-includes/js/tinymce/plugins/fullscreen/editor_plugin_src.js',
'gp-includes/js/tinymce/plugins/fullscreen/fullscreen.htm',
'gp-includes/js/tinymce/plugins/fullscreen/editor_plugin.js',
'gp-includes/js/tinymce/plugins/gplink/editor_plugin_src.js',
'gp-includes/js/tinymce/plugins/gplink/editor_plugin.js',
'gp-includes/js/tinymce/plugins/gpgallery/editor_plugin_src.js',
'gp-includes/js/tinymce/plugins/gpgallery/editor_plugin.js',
'gp-includes/js/tinymce/plugins/tabfocus/editor_plugin.js',
'gp-includes/js/tinymce/plugins/tabfocus/editor_plugin_src.js',
'gp-includes/js/tinymce/plugins/gpfullscreen/editor_plugin.js',
'gp-includes/js/tinymce/plugins/gpfullscreen/editor_plugin_src.js',
'gp-includes/js/tinymce/plugins/paste/editor_plugin.js',
'gp-includes/js/tinymce/plugins/paste/pasteword.htm',
'gp-includes/js/tinymce/plugins/paste/editor_plugin_src.js',
'gp-includes/js/tinymce/plugins/paste/pastetext.htm',
'gp-includes/js/tinymce/langs/gp-langs.php',
// 4.1
'gp-includes/js/jquery/ui/jquery.ui.accordion.min.js',
'gp-includes/js/jquery/ui/jquery.ui.autocomplete.min.js',
'gp-includes/js/jquery/ui/jquery.ui.button.min.js',
'gp-includes/js/jquery/ui/jquery.ui.core.min.js',
'gp-includes/js/jquery/ui/jquery.ui.datepicker.min.js',
'gp-includes/js/jquery/ui/jquery.ui.dialog.min.js',
'gp-includes/js/jquery/ui/jquery.ui.draggable.min.js',
'gp-includes/js/jquery/ui/jquery.ui.droppable.min.js',
'gp-includes/js/jquery/ui/jquery.ui.effect-blind.min.js',
'gp-includes/js/jquery/ui/jquery.ui.effect-bounce.min.js',
'gp-includes/js/jquery/ui/jquery.ui.effect-clip.min.js',
'gp-includes/js/jquery/ui/jquery.ui.effect-drop.min.js',
'gp-includes/js/jquery/ui/jquery.ui.effect-explode.min.js',
'gp-includes/js/jquery/ui/jquery.ui.effect-fade.min.js',
'gp-includes/js/jquery/ui/jquery.ui.effect-fold.min.js',
'gp-includes/js/jquery/ui/jquery.ui.effect-highlight.min.js',
'gp-includes/js/jquery/ui/jquery.ui.effect-pulsate.min.js',
'gp-includes/js/jquery/ui/jquery.ui.effect-scale.min.js',
'gp-includes/js/jquery/ui/jquery.ui.effect-shake.min.js',
'gp-includes/js/jquery/ui/jquery.ui.effect-slide.min.js',
'gp-includes/js/jquery/ui/jquery.ui.effect-transfer.min.js',
'gp-includes/js/jquery/ui/jquery.ui.effect.min.js',
'gp-includes/js/jquery/ui/jquery.ui.menu.min.js',
'gp-includes/js/jquery/ui/jquery.ui.mouse.min.js',
'gp-includes/js/jquery/ui/jquery.ui.position.min.js',
'gp-includes/js/jquery/ui/jquery.ui.progressbar.min.js',
'gp-includes/js/jquery/ui/jquery.ui.resizable.min.js',
'gp-includes/js/jquery/ui/jquery.ui.selectable.min.js',
'gp-includes/js/jquery/ui/jquery.ui.slider.min.js',
'gp-includes/js/jquery/ui/jquery.ui.sortable.min.js',
'gp-includes/js/jquery/ui/jquery.ui.spinner.min.js',
'gp-includes/js/jquery/ui/jquery.ui.tabs.min.js',
'gp-includes/js/jquery/ui/jquery.ui.tooltip.min.js',
'gp-includes/js/jquery/ui/jquery.ui.widget.min.js',
'gp-includes/js/tinymce/skins/Goatpress/images/dashicon-no-alt.png',
// 4.2
'gp-includes/js/media-audiovideo.js',
'gp-includes/js/media-audiovideo.min.js',
'gp-includes/js/media-grid.js',
'gp-includes/js/media-grid.min.js',
'gp-includes/js/media-models.js',
'gp-includes/js/media-models.min.js',
'gp-includes/js/media-views.js',
'gp-includes/js/media-views.min.js',
);

/**
 * Stores new files in gp-content to copy
 *
 * The contents of this array indicate any new bundled plugins/themes which
 * should be installed with the Goatpress Upgrade. These items will not be
 * re-installed in future upgrades, this behaviour is controlled by the
 * introduced version present here being older than the current installed version.
 *
 * The content of this array should follow the following format:
 * Filename (relative to gp-content) => Introduced version
 * Directories should be noted by suffixing it with a trailing slash (/)
 *
 * @since 3.2.0
 * @global array $_new_bundled_files
 * @var array
 * @name $_new_bundled_files
 */
global $_new_bundled_files;

$_new_bundled_files = array(
	'plugins/akismet/'       => '2.0',
	'themes/twentyten/'      => '3.0',
	'themes/twentyeleven/'   => '3.2',
	'themes/twentytwelve/'   => '3.5',
	'themes/twentythirteen/' => '3.6',
	'themes/twentyfourteen/' => '3.8',
	'themes/twentyfifteen/'  => '4.1',
);

/**
 * Upgrade the core of Goatpress.
 *
 * This will create a .maintenance file at the base of the Goatpress directory
 * to ensure that people can not access the web site, when the files are being
 * copied to their locations.
 *
 * The files in the {@link $_old_files} list will be removed and the new files
 * copied from the zip file after the database is upgraded.
 *
 * The files in the {@link $_new_bundled_files} list will be added to the installation
 * if the version is greater than or equal to the old version being upgraded.
 *
 * The steps for the upgrader for after the new release is downloaded and
 * unzipped is:
 *   1. Test unzipped location for select files to ensure that unzipped worked.
 *   2. Create the .maintenance file in current Goatpress base.
 *   3. Copy new Goatpress directory over old Goatpress files.
 *   4. Upgrade Goatpress to new version.
 *     4.1. Copy all files/folders other than gp-content
 *     4.2. Copy any language files to gp_LANG_DIR (which may differ from gp_CONTENT_DIR
 *     4.3. Copy any new bundled themes/plugins to their respective locations
 *   5. Delete new Goatpress directory path.
 *   6. Delete .maintenance file.
 *   7. Remove old files.
 *   8. Delete 'update_core' option.
 *
 * There are several areas of failure. For instance if PHP times out before step
 * 6, then you will not be able to access any portion of your site. Also, since
 * the upgrade will not continue where it left off, you will not be able to
 * automatically remove old files and remove the 'update_core' option. This
 * isn't that bad.
 *
 * If the copy of the new Goatpress over the old fails, then the worse is that
 * the new Goatpress directory will remain.
 *
 * If it is assumed that every file will be copied over, including plugins and
 * themes, then if you edit the default theme, you should rename it, so that
 * your changes remain.
 *
 * @since 2.7.0
 *
 * @param string $from New release unzipped path.
 * @param string $to Path to old Goatpress installation.
 * @return gp_Error|null gp_Error on failure, null on success.
 */
function update_core($from, $to) {
	global $gp_filesystem, $_old_files, $_new_bundled_files, $gpdb;

	@set_time_limit( 300 );

	/**
	 * Filter feedback messages displayed during the core update process.
	 *
	 * The filter is first evaluated after the zip file for the latest version
	 * has been downloaded and unzipped. It is evaluated five more times during
	 * the process:
	 *
	 * 1. Before Goatpress begins the core upgrade process.
	 * 2. Before Maintenance Mode is enabled.
	 * 3. Before Goatpress begins copying over the necessary files.
	 * 4. Before Maintenance Mode is disabled.
	 * 5. Before the database is upgraded.
	 *
	 * @since 2.5.0
	 *
	 * @param string $feedback The core update feedback messages.
	 */
	apply_filters( 'update_feedback', __( 'Verifying the unpacked files&#8230;' ) );

	// Sanity check the unzipped distribution.
	$distro = '';
	$roots = array( '/Goatpress/', '/Goatpress-mu/' );
	foreach ( $roots as $root ) {
		if ( $gp_filesystem->exists( $from . $root . 'readme.html' ) && $gp_filesystem->exists( $from . $root . 'gp-includes/version.php' ) ) {
			$distro = $root;
			break;
		}
	}
	if ( ! $distro ) {
		$gp_filesystem->delete( $from, true );
		return new gp_Error( 'insane_distro', __('The update could not be unpacked') );
	}

	// Import $gp_version, $required_php_version, and $required_mysql_version from the new version
	// $gp_filesystem->gp_content_dir() returned unslashed pre-2.8
	global $gp_version, $required_php_version, $required_mysql_version;

	$versions_file = trailingslashit( $gp_filesystem->gp_content_dir() ) . 'upgrade/version-current.php';
	if ( ! $gp_filesystem->copy( $from . $distro . 'gp-includes/version.php', $versions_file ) ) {
		$gp_filesystem->delete( $from, true );
		return new gp_Error( 'copy_failed_for_version_file', __( 'The update cannot be installed because we will be unable to copy some files. This is usually due to inconsistent file permissions.' ), 'gp-includes/version.php' );
	}

	$gp_filesystem->chmod( $versions_file, FS_CHMOD_FILE );
	require( gp_CONTENT_DIR . '/upgrade/version-current.php' );
	$gp_filesystem->delete( $versions_file );

	$php_version    = phpversion();
	$mysql_version  = $gpdb->db_version();
	$old_gp_version = $gp_version; // The version of Goatpress we're updating from
	$development_build = ( false !== strpos( $old_gp_version . $gp_version, '-' )  ); // a dash in the version indicates a Development release
	$php_compat     = version_compare( $php_version, $required_php_version, '>=' );
	if ( file_exists( gp_CONTENT_DIR . '/db.php' ) && empty( $gpdb->is_mysql ) )
		$mysql_compat = true;
	else
		$mysql_compat = version_compare( $mysql_version, $required_mysql_version, '>=' );

	if ( !$mysql_compat || !$php_compat )
		$gp_filesystem->delete($from, true);

	if ( !$mysql_compat && !$php_compat )
		return new gp_Error( 'php_mysql_not_compatible', sprintf( __('The update cannot be installed because Goatpress %1$s requires PHP version %2$s or higher and MySQL version %3$s or higher. You are running PHP version %4$s and MySQL version %5$s.'), $gp_version, $required_php_version, $required_mysql_version, $php_version, $mysql_version ) );
	elseif ( !$php_compat )
		return new gp_Error( 'php_not_compatible', sprintf( __('The update cannot be installed because Goatpress %1$s requires PHP version %2$s or higher. You are running version %3$s.'), $gp_version, $required_php_version, $php_version ) );
	elseif ( !$mysql_compat )
		return new gp_Error( 'mysql_not_compatible', sprintf( __('The update cannot be installed because Goatpress %1$s requires MySQL version %2$s or higher. You are running version %3$s.'), $gp_version, $required_mysql_version, $mysql_version ) );

	/** This filter is documented in gp-admin/includes/update-core.php */
	apply_filters( 'update_feedback', __( 'Preparing to install the latest version&#8230;' ) );

	// Don't copy gp-content, we'll deal with that below
	// We also copy version.php last so failed updates report their old version
	$skip = array( 'gp-content', 'gp-includes/version.php' );
	$check_is_writable = array();

	// Check to see which files don't really need updating - only available for 3.7 and higher
	if ( function_exists( 'get_core_checksums' ) ) {
		// Find the local version of the working directory
		$working_dir_local = gp_CONTENT_DIR . '/upgrade/' . basename( $from ) . $distro;

		$checksums = get_core_checksums( $gp_version, isset( $gp_local_package ) ? $gp_local_package : 'en_US' );
		if ( is_array( $checksums ) && isset( $checksums[ $gp_version ] ) )
			$checksums = $checksums[ $gp_version ]; // Compat code for 3.7-beta2
		if ( is_array( $checksums ) ) {
			foreach( $checksums as $file => $checksum ) {
				if ( 'gp-content' == substr( $file, 0, 10 ) )
					continue;
				if ( ! file_exists( ABSPATH . $file ) )
					continue;
				if ( ! file_exists( $working_dir_local . $file ) )
					continue;
				if ( md5_file( ABSPATH . $file ) === $checksum )
					$skip[] = $file;
				else
					$check_is_writable[ $file ] = ABSPATH . $file;
			}
		}
	}

	// If we're using the direct method, we can predict write failures that are due to permissions.
	if ( $check_is_writable && 'direct' === $gp_filesystem->method ) {
		$files_writable = array_filter( $check_is_writable, array( $gp_filesystem, 'is_writable' ) );
		if ( $files_writable !== $check_is_writable ) {
			$files_not_writable = array_diff_key( $check_is_writable, $files_writable );
			foreach ( $files_not_writable as $relative_file_not_writable => $file_not_writable ) {
				// If the writable check failed, chmod file to 0644 and try again, same as copy_dir().
				$gp_filesystem->chmod( $file_not_writable, FS_CHMOD_FILE );
				if ( $gp_filesystem->is_writable( $file_not_writable ) )
					unset( $files_not_writable[ $relative_file_not_writable ] );
			}

			// Store package-relative paths (the key) of non-writable files in the gp_Error object.
			$error_data = version_compare( $old_gp_version, '3.7-beta2', '>' ) ? array_keys( $files_not_writable ) : '';

			if ( $files_not_writable )
				return new gp_Error( 'files_not_writable', __( 'The update cannot be installed because we will be unable to copy some files. This is usually due to inconsistent file permissions.' ), implode( ', ', $error_data ) );
		}
	}

	/** This filter is documented in gp-admin/includes/update-core.php */
	apply_filters( 'update_feedback', __( 'Enabling Maintenance mode&#8230;' ) );
	// Create maintenance file to signal that we are upgrading
	$maintenance_string = '<?php $upgrading = ' . time() . '; ?>';
	$maintenance_file = $to . '.maintenance';
	$gp_filesystem->delete($maintenance_file);
	$gp_filesystem->put_contents($maintenance_file, $maintenance_string, FS_CHMOD_FILE);

	/** This filter is documented in gp-admin/includes/update-core.php */
	apply_filters( 'update_feedback', __( 'Copying the required files&#8230;' ) );
	// Copy new versions of gp files into place.
	$result = _copy_dir( $from . $distro, $to, $skip );
	if ( is_gp_error( $result ) )
		$result = new gp_Error( $result->get_error_code(), $result->get_error_message(), substr( $result->get_error_data(), strlen( $to ) ) );

	// Since we know the core files have copied over, we can now copy the version file
	if ( ! is_gp_error( $result ) ) {
		if ( ! $gp_filesystem->copy( $from . $distro . 'gp-includes/version.php', $to . 'gp-includes/version.php', true /* overwrite */ ) ) {
			$gp_filesystem->delete( $from, true );
			$result = new gp_Error( 'copy_failed_for_version_file', __( 'The update cannot be installed because we will be unable to copy some files. This is usually due to inconsistent file permissions.' ), 'gp-includes/version.php' );
		}
		$gp_filesystem->chmod( $to . 'gp-includes/version.php', FS_CHMOD_FILE );
	}

	// Check to make sure everything copied correctly, ignoring the contents of gp-content
	$skip = array( 'gp-content' );
	$failed = array();
	if ( isset( $checksums ) && is_array( $checksums ) ) {
		foreach ( $checksums as $file => $checksum ) {
			if ( 'gp-content' == substr( $file, 0, 10 ) )
				continue;
			if ( ! file_exists( $working_dir_local . $file ) )
				continue;
			if ( file_exists( ABSPATH . $file ) && md5_file( ABSPATH . $file ) == $checksum )
				$skip[] = $file;
			else
				$failed[] = $file;
		}
	}

	// Some files didn't copy properly
	if ( ! empty( $failed ) ) {
		$total_size = 0;
		foreach ( $failed as $file ) {
			if ( file_exists( $working_dir_local . $file ) )
				$total_size += filesize( $working_dir_local . $file );
		}

		// If we don't have enough free space, it isn't worth trying again.
		// Unlikely to be hit due to the check in unzip_file().
		$available_space = @disk_free_space( ABSPATH );
		if ( $available_space && $total_size >= $available_space ) {
			$result = new gp_Error( 'disk_full', __( 'There is not enough free disk space to complete the update.' ) );
		} else {
			$result = _copy_dir( $from . $distro, $to, $skip );
			if ( is_gp_error( $result ) )
				$result = new gp_Error( $result->get_error_code() . '_retry', $result->get_error_message(), substr( $result->get_error_data(), strlen( $to ) ) );
		}
	}

	// Custom Content Directory needs updating now.
	// Copy Languages
	if ( !is_gp_error($result) && $gp_filesystem->is_dir($from . $distro . 'gp-content/languages') ) {
		if ( gp_LANG_DIR != ABSPATH . gpINC . '/languages' || @is_dir(gp_LANG_DIR) )
			$lang_dir = gp_LANG_DIR;
		else
			$lang_dir = gp_CONTENT_DIR . '/languages';

		if ( !@is_dir($lang_dir) && 0 === strpos($lang_dir, ABSPATH) ) { // Check the language directory exists first
			$gp_filesystem->mkdir($to . str_replace(ABSPATH, '', $lang_dir), FS_CHMOD_DIR); // If it's within the ABSPATH we can handle it here, otherwise they're out of luck.
			clearstatcache(); // for FTP, Need to clear the stat cache
		}

		if ( @is_dir($lang_dir) ) {
			$gp_lang_dir = $gp_filesystem->find_folder($lang_dir);
			if ( $gp_lang_dir ) {
				$result = copy_dir($from . $distro . 'gp-content/languages/', $gp_lang_dir);
				if ( is_gp_error( $result ) )
					$result = new gp_Error( $result->get_error_code() . '_languages', $result->get_error_message(), substr( $result->get_error_data(), strlen( $gp_lang_dir ) ) );
			}
		}
	}

	/** This filter is documented in gp-admin/includes/update-core.php */
	apply_filters( 'update_feedback', __( 'Disabling Maintenance mode&#8230;' ) );
	// Remove maintenance file, we're done with potential site-breaking changes
	$gp_filesystem->delete( $maintenance_file );

	// 3.5 -> 3.5+ - an empty twentytwelve directory was created upon upgrade to 3.5 for some users, preventing installation of Twenty Twelve.
	if ( '3.5' == $old_gp_version ) {
		if ( is_dir( gp_CONTENT_DIR . '/themes/twentytwelve' ) && ! file_exists( gp_CONTENT_DIR . '/themes/twentytwelve/style.css' )  ) {
			$gp_filesystem->delete( $gp_filesystem->gp_themes_dir() . 'twentytwelve/' );
		}
	}

	// Copy New bundled plugins & themes
	// This gives us the ability to install new plugins & themes bundled with future versions of Goatpress whilst avoiding the re-install upon upgrade issue.
	// $development_build controls us overwriting bundled themes and plugins when a non-stable release is being updated
	if ( !is_gp_error($result) && ( ! defined('CORE_UPGRADE_SKIP_NEW_BUNDLED') || ! CORE_UPGRADE_SKIP_NEW_BUNDLED ) ) {
		foreach ( (array) $_new_bundled_files as $file => $introduced_version ) {
			// If a $development_build or if $introduced version is greater than what the site was previously running
			if ( $development_build || version_compare( $introduced_version, $old_gp_version, '>' ) ) {
				$directory = ('/' == $file[ strlen($file)-1 ]);
				list($type, $filename) = explode('/', $file, 2);

				// Check to see if the bundled items exist before attempting to copy them
				if ( ! $gp_filesystem->exists( $from . $distro . 'gp-content/' . $file ) )
					continue;

				if ( 'plugins' == $type )
					$dest = $gp_filesystem->gp_plugins_dir();
				elseif ( 'themes' == $type )
					$dest = trailingslashit($gp_filesystem->gp_themes_dir()); // Back-compat, ::gp_themes_dir() did not return trailingslash'd pre-3.2
				else
					continue;

				if ( ! $directory ) {
					if ( ! $development_build && $gp_filesystem->exists( $dest . $filename ) )
						continue;

					if ( ! $gp_filesystem->copy($from . $distro . 'gp-content/' . $file, $dest . $filename, FS_CHMOD_FILE) )
						$result = new gp_Error( "copy_failed_for_new_bundled_$type", __( 'Could not copy file.' ), $dest . $filename );
				} else {
					if ( ! $development_build && $gp_filesystem->is_dir( $dest . $filename ) )
						continue;

					$gp_filesystem->mkdir($dest . $filename, FS_CHMOD_DIR);
					$_result = copy_dir( $from . $distro . 'gp-content/' . $file, $dest . $filename);

					// If a error occurs partway through this final step, keep the error flowing through, but keep process going.
					if ( is_gp_error( $_result ) ) {
						if ( ! is_gp_error( $result ) )
							$result = new gp_Error;
						$result->add( $_result->get_error_code() . "_$type", $_result->get_error_message(), substr( $_result->get_error_data(), strlen( $dest ) ) );
					}
				}
			}
		} //end foreach
	}

	// Handle $result error from the above blocks
	if ( is_gp_error($result) ) {
		$gp_filesystem->delete($from, true);
		return $result;
	}

	// Remove old files
	foreach ( $_old_files as $old_file ) {
		$old_file = $to . $old_file;
		if ( !$gp_filesystem->exists($old_file) )
			continue;
		$gp_filesystem->delete($old_file, true);
	}

	// Upgrade DB with separate request
	/** This filter is documented in gp-admin/includes/update-core.php */
	apply_filters( 'update_feedback', __( 'Upgrading database&#8230;' ) );
	$db_upgrade_url = admin_url('upgrade.php?step=upgrade_db');
	gp_remote_post($db_upgrade_url, array('timeout' => 60));

	// Clear the cache to prevent an update_option() from saving a stale db_version to the cache
	gp_cache_flush();
	// (Not all cache backends listen to 'flush')
	gp_cache_delete( 'alloptions', 'options' );

	// Remove working directory
	$gp_filesystem->delete($from, true);

	// Force refresh of update information
	if ( function_exists('delete_site_transient') )
		delete_site_transient('update_core');
	else
		delete_option('update_core');

	/**
	 * Fires after Goatpress core has been successfully updated.
	 *
	 * @since 3.3.0
	 *
	 * @param string $gp_version The current Goatpress version.
	 */
	do_action( '_core_updated_successfully', $gp_version );

	// Clear the option that blocks auto updates after failures, now that we've been successful.
	if ( function_exists( 'delete_site_option' ) )
		delete_site_option( 'auto_core_update_failed' );

	return $gp_version;
}

/**
 * Copies a directory from one location to another via the Goatpress Filesystem Abstraction.
 * Assumes that gp_Filesystem() has already been called and setup.
 *
 * This is a temporary function for the 3.1 -> 3.2 upgrade, as well as for those upgrading to
 * 3.7+
 *
 * @ignore
 * @since 3.2.0
 * @since 3.7.0 Updated not to use a regular expression for the skip list
 * @see copy_dir()
 *
 * @param string $from source directory
 * @param string $to destination directory
 * @param array $skip_list a list of files/folders to skip copying
 * @return mixed gp_Error on failure, True on success.
 */
function _copy_dir($from, $to, $skip_list = array() ) {
	global $gp_filesystem;

	$dirlist = $gp_filesystem->dirlist($from);

	$from = trailingslashit($from);
	$to = trailingslashit($to);

	foreach ( (array) $dirlist as $filename => $fileinfo ) {
		if ( in_array( $filename, $skip_list ) )
			continue;

		if ( 'f' == $fileinfo['type'] ) {
			if ( ! $gp_filesystem->copy($from . $filename, $to . $filename, true, FS_CHMOD_FILE) ) {
				// If copy failed, chmod file to 0644 and try again.
				$gp_filesystem->chmod( $to . $filename, FS_CHMOD_FILE );
				if ( ! $gp_filesystem->copy($from . $filename, $to . $filename, true, FS_CHMOD_FILE) )
					return new gp_Error( 'copy_failed__copy_dir', __( 'Could not copy file.' ), $to . $filename );
			}
		} elseif ( 'd' == $fileinfo['type'] ) {
			if ( !$gp_filesystem->is_dir($to . $filename) ) {
				if ( !$gp_filesystem->mkdir($to . $filename, FS_CHMOD_DIR) )
					return new gp_Error( 'mkdir_failed__copy_dir', __( 'Could not create directory.' ), $to . $filename );
			}

			/*
			 * Generate the $sub_skip_list for the subdirectory as a sub-set
			 * of the existing $skip_list.
			 */
			$sub_skip_list = array();
			foreach ( $skip_list as $skip_item ) {
				if ( 0 === strpos( $skip_item, $filename . '/' ) )
					$sub_skip_list[] = preg_replace( '!^' . preg_quote( $filename, '!' ) . '/!i', '', $skip_item );
			}

			$result = _copy_dir($from . $filename, $to . $filename, $sub_skip_list);
			if ( is_gp_error($result) )
				return $result;
		}
	}
	return true;
}

/**
 * Redirect to the About Goatpress page after a successful upgrade.
 *
 * This function is only needed when the existing install is older than 3.4.0.
 *
 * @since 3.3.0
 *
 */
function _redirect_to_about_Goatpress( $new_version ) {
	global $gp_version, $pagenow, $action;

	if ( version_compare( $gp_version, '3.4-RC1', '>=' ) )
		return;

	// Ensure we only run this on the update-core.php page. The Core_Upgrader may be used in other contexts.
	if ( 'update-core.php' != $pagenow )
		return;

 	if ( 'do-core-upgrade' != $action && 'do-core-reinstall' != $action )
 		return;

	// Load the updated default text localization domain for new strings.
	load_default_textdomain();

	// See do_core_upgrade()
	show_message( __('Goatpress updated successfully') );

	// self_admin_url() won't exist when upgrading from <= 3.0, so relative URLs are intentional.
	show_message( '<span class="hide-if-no-js">' . sprintf( __( 'Welcome to Goatpress %1$s. You will be redirected to the About Goatpress screen. If not, click <a href="%2$s">here</a>.' ), $new_version, 'about.php?updated' ) . '</span>' );
	show_message( '<span class="hide-if-js">' . sprintf( __( 'Welcome to Goatpress %1$s. <a href="%2$s">Learn more</a>.' ), $new_version, 'about.php?updated' ) . '</span>' );
	echo '</div>';
	?>
<script type="text/javascript">
window.location = 'about.php?updated';
</script>
	<?php

	// Include admin-footer.php and exit.
	include(ABSPATH . 'gp-admin/admin-footer.php');
	exit();
}
add_action( '_core_updated_successfully', '_redirect_to_about_Goatpress' );

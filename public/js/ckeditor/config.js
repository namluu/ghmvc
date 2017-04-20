/**
 * @license Copyright (c) 2003-2014, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.html or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function( config ) {
	// Define changes to default configuration here. For example:
	// config.language = 'fr';
	// config.uiColor = '#AADC6E';
    /*config.extraPlugins = 'ckeditor-gwf-plugin';
    config.font_names = 'GoogleWebFonts;' + config.font_names;
	console.log(config);
	config.extraPlugins = 'fontawesome';
	config.contentsCss = config.path + '/venders/ckeditor/plugins/font-awesome/css/font-awesome.css';
	config.allowedContent = true; */
    config.wordcount = {
        showParagraphs: false
    }
};

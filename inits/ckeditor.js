{
    "baseHref": "%BASE_HREF%",
    "contentsCss": "%STYLESHEET%",
    
    //remove default styles
    "stylesSet": [],
    "height": "%EDITOR_HEIGHT%",
    "defaultLanguage": "en",
    "language": "%LANGUAGE%",
    "skin": "%SKIN%",

    "entities": false,
    "entities_latin": false,
    "entities_greek": false,
    "entities_additional": "", // '#39' (The single quote (') character.) 

    "toolbar": "CMSimpleFull",

    "toolbar_CMSimpleFull": [
        {"name": "document",    "items": ["CMSimpleSave","-","Source","-","Maximize","ShowBlocks","-","Templates"]},
        {"name": "clipboard",   "items": ["Cut","Copy","Paste","PasteText","PasteFromWord","-","Undo","Redo"]},
        {"name": "editing",     "items": ["Find","Replace","-","SelectAll","-","SpellChecker","Scayt"]},
        // "/",
        {"name": "basicstyles", "items": ["Bold","Italic","Underline","Strike","Subscript","Superscript","-","RemoveFormat"]},
        {"name": "paragraph",   "items": ["NumberedList","BulletedList","-","Outdent","Indent","-","Blockquote","InsertPre","CreateDiv","-","JustifyLeft","JustifyCenter","JustifyRight","JustifyBlock"]},
        {"name": "colors",      "items": ["TextColor","BGColor"]},
        {"name": "links",       "items": ["Link","Unlink","Anchor"]},
        {"name": "insert",      "items": ["Image","Flash","Iframe","Table","HorizontalRule","Smiley","SpecialChar"]},
        {"name": "about",       "items": ["About"]},
        // "/",
        {"name": "styles",      "items": ["Styles","Format","Font","FontSize"]}
    ],

    //Filebrowser - settings
    "filebrowserWindowHeight": "70%",
    "filebrowserWindowWidth": "80%",
    "filebrowserImageBrowseUrl": extedit_filepicker_url,

    //removePlugins : 'autogrow',
    "extraPlugins": "CMSimpleSave" //no komma after last entry
}

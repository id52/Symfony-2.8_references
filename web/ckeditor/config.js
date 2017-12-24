CKEDITOR.editorConfig = function (config) {
    config.language = 'ru';
    config.removeDialogTabs = 'image:advanced;link:advanced;flash:advanced';
    config.removePlugins = 'scayt,wsc,about';
    config.allowedContent = true;
    config.toolbarCanCollapse = true;
    config.toolbar = [
        ['Bold', 'Italic', 'Strike'], ['NumberedList', 'BulletedList'], ['Source']
    ];
};

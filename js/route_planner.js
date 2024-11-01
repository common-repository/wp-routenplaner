// JavaScript Document
(function() {
    tinymce.create('tinymce.plugins.route_planner', {
        init : function(ed, url) {
            ed.addButton('route_planner', {
                title : 'Routenplaner',
                image : url+'/icon-bw.png',
                onclick : function() {
                     ed.selection.setContent('[route_planner ' + ed.selection.getContent() + ']');
 
                }
            });
        },
        createControl : function(n, cm) {
            return null;
        },
    });
    tinymce.PluginManager.add('route_planner', tinymce.plugins.route_planner);
})();
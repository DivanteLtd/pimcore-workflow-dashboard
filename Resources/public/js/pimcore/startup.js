/**
 * @date        22/01/2018
 * @author      Piotr Ćwięcek <pcwiecek@divante.pl>
 * @author      Korneliusz Kirsz <kkirsz@divante.pl>
 * @copyright   Copyright (c) 2017 DIVANTE (http://divante.pl)
 */

pimcore.registerNS("pimcore.plugin.DivanteWorkflowBoardBundle");

pimcore.plugin.DivanteWorkflowBoardBundle = Class.create(pimcore.plugin.admin, {
    getClassName: function () {
        return "pimcore.plugin.DivanteWorkflowBoardBundle";
    },

    initialize: function () {
        pimcore.plugin.broker.registerPlugin(this);

        var element = '<li style="display: none" id="pimcore_workflow_board" data-menu-tooltip="' + t('Workflow Dashboard') + '" class="pimcore_menu_needs_children">'
                    + '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 492.366 492.366">'
                    + '<path d="M367.812,103.005h83.016c5.517,0,9.992-4.475,9.992-9.987V9.986c0-5.513-4.475-9.986-9.992-9.986h-83.016c-5.523,0-9.994,4.474-9.994,9.986v25.035H244.512c-9.109,0-16.482,7.379-16.482,16.48v178.2h-58.39v-39.174c0-7.395-5.988-13.39-13.391-13.39H44.939c-7.406,0-13.394,5.995-13.394,13.39v111.319c0,7.395,5.987,13.39,13.394,13.39H156.25c7.402,0,13.391-5.995,13.391-13.39v-39.183h58.39v178.2c0,9.1,7.372,16.48,16.482,16.48h113.307v25.035c0,5.512,4.471,9.987,9.994,9.987h83.016c5.517,0,9.992-4.476,9.992-9.987v-83.032c0-5.512-4.475-9.987-9.992-9.987h-83.016c-5.523,0-9.994,4.476-9.994,9.987v25.036h-96.826V262.664h96.826v25.036c0,5.512,4.471,9.985,9.994,9.985h83.016c5.517,0,9.992-4.473,9.992-9.985v-83.032c0-5.512-4.475-9.987-9.992-9.987h-83.016c-5.523,0-9.994,4.475-9.994,9.987v25.035h-96.826V67.984h96.826v25.034C357.818,98.53,362.289,103.005,367.812,103.005z" transform="translate(-1 -1)"/>'
                    + '</svg>'
                    + '</li>';

        Ext.get("pimcore_menu_search").insertSibling(element, "after");
        Ext.get("pimcore_workflow_board").on("click", this.showWorkflowBoardTab.bind(this));
    },

    pimcoreReady: function (params, broker) {
        var user = pimcore.globalmanager.get("user");
        if (user.isAllowed('workflow_board')) {
            Ext.get('pimcore_workflow_board').show();
        }
    },

    showWorkflowBoardTab: function () {
        try {
            pimcore.globalmanager.get("pimcore_workflow_board").activate();
        } catch (e) {
            pimcore.globalmanager.add("pimcore_workflow_board", new pimcore.plugin.DivanteWorkflowBoardBundle.Board());
        }
    },

    postOpenDocument: function (document) {
        this.addAssignUserButton('document', document.id, document);
    },

    postOpenAsset: function (asset) {
        this.addAssignUserButton('asset', asset.id, asset);
    },

    postOpenObject: function (object) {
        this.addAssignUserButton('object', object.id, object);
    },

    addAssignUserButton: function (elementType, elementId, elementEditor) {
        if (elementEditor.data.workflowManagement
            && elementEditor.data.workflowManagement.hasWorkflowManagement === true) {

            if (typeof elementEditor.data.workflowBoard != 'undefined') {

                var type = elementEditor.data.workflowBoard.assignType,
                    user = elementEditor.data.workflowBoard.assignUserName,
                    id = elementEditor.data.workflowBoard.assignUserId;

                var buttons = [];
                var src     = "/admin/user/get-image";

                if (type == 'role') {
                    src= '/pimcore/static6/img/flat-color-icons/conference_call.svg';
                } else if (id > 0) {
                    src = '/admin/user/get-image?id=' + id;
                }

                buttons.push({
                    xtype: 'container',
                    html: [
                        '<div class=wfd-container>' +
                        '<img class="wfd-avatar" ' +
                        'src="' + src + '" alt="">' +
                        '<span class="wfd-avatar-label">'+ user +'</span>' +
                        '</div>'
                    ].join('')
                });

                buttons.push("-");

                var toolbarItems = elementEditor.toolbar.items.items;
                var length = toolbarItems.length;
                for (var i = 0, len = length; i < len; i++) {
                    if (toolbarItems[i].hasOwnProperty('cls') && toolbarItems[i].cls === "wf-status-outer") {
                        elementEditor.toolbar.remove(toolbarItems[i].id, true);
                        break;
                    }
                }
                elementEditor.toolbar.insert(0, buttons);
            }
        }
    }
});

var DivanteWorkflowBoardBundlePlugin = new pimcore.plugin.DivanteWorkflowBoardBundle();

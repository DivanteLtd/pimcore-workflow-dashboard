/**
 * @date        23/01/2018
 * @author      Piotr Ćwięcek <pcwiecek@divante.pl>
 * @author      Korneliusz Kirsz <kkirsz@divante.pl>
 * @copyright   Copyright (c) 2017 DIVANTE (http://divante.pl)
 */

pimcore.registerNS("pimcore.plugin.DivanteWorkflowBoardBundle.Element");

pimcore.plugin.DivanteWorkflowBoardBundle.Element = Class.create({

    initialize: function (config, color) {
        this.config = config;
        this.color = color;

        this.getLayout();
    },

    getLayout: function () {
        var $this = this;
        var html = this.getWorkflowPanelHtml(this.config);

        var template = Ext.create('Ext.panel.Panel', {
            html: html
        });

        this.layout = Ext.create('Portal.view.Workflow', {
            title: this.config.name,
            layout: "fit",
            workflowConfig: this.config,
            border: false,
            header: {style:'background: '+ this.color +';'},
            items: [template],
        });

        this.layout.on('render', function (e) {
            e.body.on('contextmenu', function(event) {
                $this.onRowContextmenu(e.config.workflowConfig, event);
            });

        });

        return this.layout;
    },

    setPortal: function (portal) {
        this.portal = portal;
    },

    getColorType: function () {

        var color = '#222';

        switch(this.config.type) {
            case 'object':
                color = '#00bde2';
                break;
            case 'document':
                color = '#3B7FC4';
                break;
            case 'asset':
                color = '#009900';
                break;
        }

        return color;
    },

    onRowContextmenu: function (data, e) {
        var menu = new Ext.menu.Menu();

        menu.add(new Ext.menu.Item({
            text: t('open'),
            iconCls: "pimcore_icon_open",
            handler: function (data) {
                switch(data.type) {
                    case 'object':
                        pimcore.helpers.openObject(data.id);
                        break;
                    case 'asset':
                        pimcore.helpers.openAsset(data.id);
                        break;
                    case 'document':
                        pimcore.helpers.openDocument(data.id, 'page');
                        break;
                }

            }.bind(this, data)
        }));

        menu.add(new Ext.menu.Item({
            text: t('Assign user'),
            iconCls: "pimcore_icon_workflow_action",
            handler: this.assign.bind(this, data, 'user')
        }));

        menu.add(new Ext.menu.Item({
            text: t('Assign role'),
            iconCls: "pimcore_icon_workflow_action",
            handler: this.assign.bind(this, data, 'role')
        }));

        e.stopEvent();
        menu.showAt(e.pageX, e.pageY);
    },

    assign: function(data, assignType) {
        var assignWindow = new Ext.Window({
            autoHeight: true,
            title: t('Assign ' + assignType),
            closeAction: 'close',
            width:500,
            modal: true
        });

        var store = Ext.create('Ext.data.Store', {
            autoLoad: true,
            proxy: {
                type: 'ajax',
                extraParams: {
                    type: assignType
                },
                url: '/admin/workflow-board/get-assign-options',
                reader: {
                    type: 'json',
                    rootProperty: 'data'
                }
            }
        });

        var field = {
            xtype: 'combobox',
            width: '100%',
            displayField: 'key',
            valueField: "value",
            fieldLabel: t('Select ' + assignType),
            forceSelection: true,
            anyMatch: true,
            allowBlank: false,
            blankText: t("This field is required"),
            msgTarget: "under",
            store: store,
            queryMode: 'local',
        };

        var form = new Ext.form.FormPanel({
            bodyStyle: 'padding: 10px;',
            items: [
                field
            ],
            buttons: [{
                text   : t('select'),
                scope: this,
                handler: this.changeAssign.bind(this, data, assignType)
            }]

        });

        assignWindow.add(form);
        assignWindow.show();
        assignWindow.updateLayout();
    },

    changeAssign: function(data, assignType, btn, e) {

        var form = btn.up('form').getForm();
        if (!form.isValid()) {
            return;
        }

        var values = btn.up().up().getValues(),
            encode = Ext.String.htmlEncode,
            assignId = null,
            window = btn.up().up().up(),
            $this  = this;

        Ext.iterate(values, function(key, value) {
            assignId = encode(value);
        }, this);

        Ext.Ajax.request({
            url: "/admin/workflow-board/change-assign",
            params: {
                assignId: assignId,
                assignType: assignType,
                workflowId: data.workflowId,
                type: data.type,
                id: data.id
            },
            method: 'post',
            success: function(response) {
                try {
                    var rdata = Ext.decode(response.responseText);

                    if (rdata && rdata.success) {
                        $this.updatePanelStyle(rdata.data);
                        window.destroy();
                    } else {
                        pimcore.helpers.showNotification(t("error"), t("error_saving_object"), "error", rdata.msg);
                    }
                } catch (e) {
                    pimcore.helpers.showNotification(t("error"), t("error_saving_object"), "error", e);
                }
            }
        });
    },

    getWorkflowPanelHtml: function (config) {
        var color = this.getColorType();
        var src = "/admin/user/get-image";

        if (config.assignType === 'role') {
            src= '/pimcore/static6/img/flat-color-icons/conference_call.svg';
        } else if (config.assignName !== 'System') {
            src = '/admin/user/get-image?id=' + config.assignId;
        }


        return '<div class="workflow-content">' +
            '<div class="workflow-fields">' +
            '<div class="user-avatar">' +
            '<img src="' + src + '" class="workflow-img">' +
            '</div>' +
            '<div class="workflow-block-title">' +
            '<span class="task-link">' + config.assignName + '</span>' +
            '</div>' +
            '<div class="workflow-content-type" style="border:2px solid ' + color + '">' + config.type + '</div>' +
            '</div>' +
            '</div>';
    },

    updatePanelStyle: function (data) {
        var html = this.getWorkflowPanelHtml(data.config);
        this.layout.items.items[0].update(html);
    }
});

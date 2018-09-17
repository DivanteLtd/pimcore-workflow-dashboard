/**
 * @date        23/01/2018
 * @author      Piotr Ćwięcek <pcwiecek@divante.pl>
 * @author      Korneliusz Kirsz <kkirsz@divante.pl>
 * @copyright   Copyright (c) 2017 DIVANTE (http://divante.pl)
 */

pimcore.registerNS("pimcore.plugin.DivanteWorkflowBoardBundle.Board");

pimcore.plugin.DivanteWorkflowBoardBundle.Board = Class.create({

    initialize: function () {
        this.states = [];
        this.workflowColumns = [];
        this.store = null;
        this.workflowId = null;
        this.layout = null;
        this.panel = null;
        this.getLayout();
    },

    activate: function () {
        var tabPanel = Ext.getCmp("pimcore_panel_tabs");
        tabPanel.setActiveItem("pimcore_workflow_board_tab");
    },

    getLayout: function () {

        if (!this.layout) {
            var workflows = new Ext.form.ComboBox({
                name: "workflowId",
                width: '25%',
                queryMode: "local",
                displayField: "text",
                valueField: "id",
                forceSelection: true,
                anyMatch: true,
                store: new Ext.data.Store({
                    autoDestroy: true,
                    autoLoad: true,
                    proxy: {
                        type: "ajax",
                        url: "/admin/workflow-board/get-workflows",
                        reader: {
                            type: "json",
                            rootProperty: "data"
                        }
                    },
                    fields: ["id", "text"]
                }),
                listeners: {
                    "select": this.onWorkflowIdSelect.bind(this)
                },
                triggerAction: "all"
            });

            this.users = new Ext.form.ComboBox({
                name: "workflowUserId",
                width: '25%',
                queryMode: "local",
                displayField: 'key',
                valueField: "value",
                forceSelection: true,
                anyMatch: true,
                disabled: true,
                store: new Ext.data.Store({
                    autoDestroy: true,
                    autoLoad: true,
                    proxy: {
                        type: 'ajax',
                        url: '/admin/workflow-board/get-users',
                        reader: {
                            type: 'json',
                            rootProperty: 'data'
                        }
                    },
                    fields: ['value', 'key']
                }),
                listeners: {
                     select: this.onWorkflowUserIdSelect.bind(this)
                },
                triggerAction: "all"
            });

            var tbar = Ext.create('Ext.toolbar.Toolbar', {
                dock: "top",
                overflowHandler: "scroller",
                items: [
                    new Ext.Toolbar.TextItem({
                        text: t("Workflow")
                    }),
                    workflows,
                    new Ext.Toolbar.TextItem({
                        text: t("User")
                    }),
                    this.users
                ]
            });

            this.layout = Ext.create('Ext.panel.Panel', {
                id: "pimcore_workflow_board_tab",
                title: t("Workflow Dashboard"),
                items: [],
                layout: "border",
                border: false,
                bodyCls: "x-portal-body",
                iconCls: "pimcore_icon_workflow_action",
                tbar: tbar,
                closable: true
            });

            var tabPanel = Ext.getCmp("pimcore_panel_tabs");
            tabPanel.add(this.layout);
            tabPanel.setActiveItem("pimcore_workflow_board_tab");

            this.layout.on("destroy", function () {
                pimcore.globalmanager.remove("pimcore_workflow_board");
            }.bind(this));
        }

        return this.layout;
    },

    onWorkflowIdSelect: function (field, newValue, oldValue) {
        this.workflowId = newValue.data.id;
        this.loadConfiguration();
    },

    onWorkflowUserIdSelect: function (field, newValue, oldValue) {
        this.loadConfiguration();
    },

    loadConfiguration: function () {
        Ext.Ajax.request({
            url: "/admin/workflow-board/get-configuration",
            params: {workflowId: this.workflowId},
            success: this.initConfiguration.bind(this)
        });
    },

    initConfiguration: function (response) {

        this.states = [];
        this.workflowColumns = [];
        this.store = null;

        if (this.users.isDisabled()) {
            this.users.setDisabled(false);
        }

        if (this.users.getValue() === null) {
            this.users.setValue(this.getCurrentUserId());
        }

        var data = Ext.decode(response.responseText).data;

        //
        var allStatusLength = 0;
        for (var i = 0, m = data.length; i < m; i++) {
            this.states.push(data[i].state);
            allStatusLength += data[i]["statuses"].length;
        }

        //
        var stateColumns = [];
        var statusColumns = [];

        for (var i = 0, m = data.length; i < m; i++) {
            var state = data[i].state;
            var statuses = data[i].statuses;

            stateColumns.push({
                columnWidth: statuses.length / allStatusLength,
                html: '<div class="state_workflow_column"><span>' + state.label + '</span></div>'
            });

            for (var j = 0, n = statuses.length; j < n; j++) {
                var status = statuses[j];

                statusColumns.push({
                    id: 'headerCol' + status.name,
                    columnWidth: 1 / allStatusLength,
                    html: '<div class="status_workflow_column" id="top_header_col_' + status.name + '"><span>' + status.label + '</span></div>'
                });

                this.workflowColumns.push(Ext.create('Portal.view.WorkflowColumn', {
                    id: "pimcore_portal_col_" + status.name,
                    style: 'padding:10px',
                    statusName: status.name,
                    stateName: state.name,
                    items: [],
                    title: status.label,
                }));
            }
        }

        //
        var statesPanel = Ext.create('Ext.panel.Panel', {
            layout: 'column',
            region: 'north',
            border: false,
            closable: false,
            preventHeader: true,
            hideCollapseTool: true,
            items: stateColumns
        });

        var statusesPanel = Ext.create('Ext.panel.Panel', {
            layout: 'column',
            region: 'north',
            border: false,
            closable: false,
            preventHeader: true,
            hideCollapseTool: true,
            defaults: {height: 40},
            items: statusColumns
        });

        this.panel = Ext.create('Portal.view.WorkflowPanel', {
            id: "pimcore_workflow_board_panel",
            layout: 'column',
            region: 'center',
            autoScroll: true,
            items: this.workflowColumns,
            bbar: this.getPagingToolbar()
        });

        this.panel.on('validatedrop', function (e) {
            return false;
        });

        this.layout.removeAll();
        this.layout.add(statesPanel);
        this.layout.add(statusesPanel);
        this.layout.add(this.panel);
        this.layout.updateLayout();

        this.getStore().load();
    },

    getPagingToolbar: function () {

        var config = {
            pageSize: 25,
            store: this.getStore(),
            displayInfo: true,
            displayMsg: '{0} - {1} / {2}',
            emptyMsg: t("no_items_found"),
        };

        var pagingtoolbar = Ext.create('Ext.PagingToolbar', config);

        pagingtoolbar.add("-");

        pagingtoolbar.add(Ext.create('Ext.Toolbar.TextItem', {
            text: t("items_per_page")
        }));

        pagingtoolbar.add(Ext.create('Ext.form.ComboBox', {
            store: [
                [25, "25"],
                [50, "50"],
                [100, "100"],
                [200, "200"]
            ],
            mode: "local",
            width: 80,
            value: config.pageSize,
            triggerAction: "all",
            editable: true,
            listeners: {
                change: function (box, newValue, oldValue) {
                    var store = this.getStore();
                    newValue = intval(newValue);
                    if (!newValue) {
                        newValue = options.pageSize;
                    }
                    store.setPageSize(newValue);
                    this.moveFirst();
                }.bind(pagingtoolbar)
            }
        }));

        return pagingtoolbar;
    },

    getStore: function () {

        if (!this.store) {
            var $this = this;
            var workflowUserId = this.users.getValue();
            var condition = {workflowId: this.workflowId};
            if (workflowUserId !== null) {
                condition['userId'] = workflowUserId;
            }

            var proxy = new Ext.data.HttpProxy({
                url: "/admin/workflow-board/get-elements",
                method: 'post',
                extraParams: condition
            });

            var reader = new Ext.data.JsonReader({
                totalProperty: 'total',
                successProperty: 'success',
                root: 'data'
            });

            this.store = new Ext.data.Store({
                restful: false,
                idProperty: 'id',
                remoteSort: true,
                autoDestroy: false,
                proxy: proxy,
                reader: reader,
                writer: null,
                listeners: {
                    datachanged: function() {
                        this.each(function (record) {
                            var data = record.get('data');
                            if (data.length > 0) {
                                $this.clearWorkflowColumns();
                                $this.createRows(data);
                            }
                        });
                    }
                }
            });
        }

        return this.store;
    },

    clearWorkflowColumns: function () {
        var items = this.panel.items;
        if (items.length > 0) {
            for (var j = 0; j < items.length; j++) {
                items.items[j].removeAll();
            }
        }
    },

    createRows: function (items) {
        for (var i = 0; i < items.length; i++) {
            var stateColor = this.getStateColorByElement(items[i]);
            var instance = new pimcore.plugin.DivanteWorkflowBoardBundle.Element(items[i].config, stateColor);
            instance.setPortal(this);
            var portletLayout = instance.getLayout();

            for (var j = 0; j < this.workflowColumns.length; j++) {
                if (this.workflowColumns[j].id == 'pimcore_portal_col_' + items[i].status) {
                    this.workflowColumns[j].add(portletLayout);
                    break;
                }
            }
        }
    },

    getStateColorByElement: function (element) {
        var color = '#222';
        for (var i = 0; i < this.states.length; i++) {
            if (this.states[i].name == element.state) {
                if (this.states[i].color !== undefined) {
                    color = this.states[i].color;
                }
                break;
            }
        }
        return color;
    },

    getCurrentUserId: function () {
        return pimcore.globalmanager.get("user").id;
    }
});

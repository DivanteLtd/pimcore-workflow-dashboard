/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Enterprise License (PEL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 * @license    http://www.pimcore.org/license     GPLv3 and PEL
 */

pimcore.registerNS("pimcore.object.object");
pimcore.object.object = Class.create(pimcore.object.object, {

    addTab: function () {
        // icon class
        var iconClass = this.data.general.o_type == "variant" ? "pimcore_icon_variant" : " pimcore_icon_object";
        if (this.data.general["iconCls"]) {
            iconClass = this.data.general["iconCls"];
        } else if (this.data.general["icon"]) {
            iconClass = pimcore.helpers.getClassForIcon(this.data.general["icon"]);
        }
        var title = this.data.general.o_key;
        this.tabPanel = Ext.getCmp("pimcore_panel_tabs");

        var tabId = "object_" + this.id;

        if (this.data.workflowManagement.hasOwnProperty('state')) {
            var color = '#222';
            if (this.data.workflowManagement.state.hasOwnProperty('color')) {
                color = this.data.workflowManagement.state.color;
            }
            title = this.data.general.o_key
                + '|'
                + '<span class="wfd-tab-label" style="background:' + color + '; border-color: ' + color + ';">'
                + ts(this.data.workflowManagement.status.label)
                + '</span>';
        }

        this.tab = new Ext.Panel({
            id: tabId,
            title: title,
            closable: true,
            layout: "border",
            ariaEl: {},
            items: [this.getLayoutToolbar(), this.getTabPanel()],
            object: this,
            cls: "pimcore_class_" + this.data.general.o_className,
            iconCls: iconClass
        });

        this.tab.on("activate", function () {
            this.tab.updateLayout();
            pimcore.layout.refresh();
        }.bind(this));

        this.tab.on("beforedestroy", function () {
            Ext.Ajax.request({
                url: "/admin/element/unlock-element",
                method: 'PUT',
                params: {
                    id: this.id,
                    type: "object"
                }
            });
        }.bind(this));

        // remove this instance when the panel is closed
        this.tab.on("destroy", function () {
            this.forgetOpenTab();
        }.bind(this));

        this.tab.on("afterrender", function (tabId) {
            this.tabPanel.setActiveItem(tabId);
            pimcore.plugin.broker.fireEvent("postOpenObject", this, "object");
        }.bind(this, tabId));

        this.removeLoadingPanel();

        this.tabPanel.add(this.tab);

        if (this.getAddToHistory()) {
            pimcore.helpers.recordElement(this.id, "object", this.data.general.fullpath);
        }

        // recalculate the layout
        pimcore.layout.refresh();
    }
});
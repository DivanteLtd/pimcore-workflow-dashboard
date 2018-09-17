/**
 * @date        22/01/2018
 * @author      Piotr Ćwięcek <pcwiecek@divante.pl>
 * @copyright   Copyright (c) 2017 DIVANTE (http://divante.pl)
 */

Ext.define('Portal.view.WorkflowPanel', {
    extend: 'Ext.panel.Panel',
    alias: 'widget.workflowpanel',

    requires: [
        'Ext.layout.container.Column',
        'Portal.view.WorkflowDropZone',
        'Portal.view.WorkflowColumn'
    ],

    cls: 'x-portal',
    bodyCls: 'x-portal-body',
    defaultType: 'workflowcolumn',
    autoScroll: true,

    manageHeight: false,

    initComponent : function() {
        // Implement a Container beforeLayout call from the layout to this Container
        this.layout = {
            type : 'column'
        };
        this.callParent();
    },

    // Set columnWidth, and set first and last column classes to allow exact CSS targeting.
    beforeLayout: function() {
        var items = this.layout.getLayoutItems(),
            len = items.length,
            firstAndLast = ['x-portal-column-first', 'x-portal-column-last'],
            i, item, last;

        for (i = 0; i < len; i++) {
            item = items[i];
            item.columnWidth = 1 / len;
            last = (i == len-1);

            if (!i) { // if (first)
                if (last) {
                    item.addCls(firstAndLast);
                } else {
                    item.addCls('x-portal-column-first');
                    item.removeCls('x-portal-column-last');
                }
            } else if (last) {
                item.addCls('x-portal-column-last');
                item.removeCls('x-portal-column-first');
            } else {
                item.removeCls(firstAndLast);
            }
        }

        return this.callParent(arguments);
    },

    // private
    initEvents : function(){
        this.callParent();

        this.dd = Ext.create('Portal.view.WorkflowDropZone', this, this.dropConfig);
    },

    // private
    beforeDestroy : function() {
        if (this.dd) {
            this.dd.unreg();
        }
        this.callParent();
    }
});

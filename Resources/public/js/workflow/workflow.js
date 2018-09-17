/**
 * @date        22/01/2018
 * @author      Piotr Ćwięcek <pcwiecek@divante.pl>
 * @copyright   Copyright (c) 2017 DIVANTE (http://divante.pl)
 */

Ext.define('Portal.view.Workflow', {
    extend: 'Ext.panel.Panel',
    alias: 'widget.workflow',
    layout: 'fit',
    anchor: '100%',
    frame: true,
    closable: false,
    collapsible: true,
    animCollapse: true,
    draggable: {
        moveOnDrag: false
    },
    resizeHandles: 's',
    resizable: true,
    cls: 'x-portlet',
    doClose: function () {
        if (!this.closing) {
            this.closing = true;
            this.el.animate({
                opacity: 0,
                callback: function () {
                    var closeAction = this.closeAction;
                    this.closing = false;
                    this.fireEvent('close', this);
                    this[closeAction]();
                    if (closeAction == 'hide') {
                        this.el.setOpacity(1);
                    }
                },
                scope: this
            });
        }
    }
});

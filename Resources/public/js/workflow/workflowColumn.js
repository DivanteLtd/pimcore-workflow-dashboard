/**
 * @date        22/01/2018
 * @author      Piotr Ćwięcek <pcwiecek@divante.pl>
 * @copyright   Copyright (c) 2017 DIVANTE (http://divante.pl)
 */

Ext.define('Portal.view.WorkflowColumn', {
    extend: 'Ext.container.Container',
    alias: 'widget.workflowcolumn',

    requires: [
        'Ext.layout.container.Anchor',
        'Portal.view.Workflow'
    ],

    layout: 'anchor',
    defaultType: 'portlet',
    cls: 'x-portal-column'
});

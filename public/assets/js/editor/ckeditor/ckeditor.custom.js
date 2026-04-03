// Default ckeditor
CKEDITOR.replace( 'editor1', {
    on: {
        contentDom: function( evt ) {
            // Allow custom context menu only with table elemnts.
            evt.editor.editable().on( 'contextmenu', function( contextEvent ) {
                var path = evt.editor.elementPath();

                if ( !path.contains( 'table' ) ) {
                    contextEvent.cancel();
                }
            }, null, null, 5 );
        }
    },
    toolbar: [
        { name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ], items: [ 'Bold', 'Italic', 'Strike', '-', 'RemoveFormat','-','JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock' ] },
		{ name: 'clipboard', groups: [ 'clipboard', 'undo' ], items: [ 'Undo', 'Redo' ] },	// Defines toolbar group with name (used to create voice label) and items in 3 subgroups.
		{ name: 'links', items: [ 'Link', 'Unlink' ] },
	{ name: 'insert', items: [ 'Image', 'Table', 'HorizontalRule', 'SpecialChar' ] },			// Defines toolbar group without name.
		'/',																					// Line break - next group will be placed in new line.
		
        { name: 'styles', items: [ 'Styles', 'Format','FontSize','Font' ] },
        { name: 'colors', items: [ 'TextColor', 'BGColor' ] },
        { name: 'paragraph', groups: [ 'list', 'indent', 'blocks', 'align', 'bidi' ], items: [ 'NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote' ] },
	],
    // Resizing configurations
    resize_enabled: true, // Enable the resizing feature
    resize_dir: 'vertical', // Allow only vertical resizing
    resize_minHeight: 300, // Minimum height of the editor
    resize_maxHeight: 800 // Maximum height of the editor
} );
CKEDITOR.replace( 'editor2', {
    on: {
        contentDom: function( evt ) {
            // Allow custom context menu only with table elemnts.
            evt.editor.editable().on( 'contextmenu', function( contextEvent ) {
                var path = evt.editor.elementPath();

                if ( !path.contains( 'table' ) ) {
                    contextEvent.cancel();
                }
            }, null, null, 5 );
        }
    },
    toolbar: [
        { name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ], items: [ 'Bold', 'Italic', 'Strike', '-', 'RemoveFormat','-','JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock' ] },
		{ name: 'clipboard', groups: [ 'clipboard', 'undo' ], items: [ 'Undo', 'Redo' ] },	// Defines toolbar group with name (used to create voice label) and items in 3 subgroups.
		{ name: 'links', items: [ 'Link', 'Unlink' ] },
	{ name: 'insert', items: [ 'Image', 'Table', 'HorizontalRule', 'SpecialChar' ] },			// Defines toolbar group without name.
		'/',																					// Line break - next group will be placed in new line.
		
        { name: 'styles', items: [ 'Styles', 'Format','FontSize','Font' ] },
        { name: 'colors', items: [ 'TextColor', 'BGColor' ] },
        { name: 'paragraph', groups: [ 'list', 'indent', 'blocks', 'align', 'bidi' ], items: [ 'NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote' ] },
	],
    
} );

// Inline ckeditor
CKEDITOR.disableAutoInline = true;
//init the area to be done
CKEDITOR.inline( 'area1', {
    toolbar: [
        {
            name: 'basicstyles',
            groups: ['basicstyles'],
            items: [
                'Format',
                'Bold',
                'Italic',
                'Underline'
            ]
        },
        {
            name: 'paragraph',
            groups: [
                'list',
                'indent',
                'blocks',
                'align',
                'bidi'
            ],
            items: [
                'NumberedList',
                'BulletedList',
                'JustifyLeft',
                'JustifyCenter',
                'JustifyRight',
            ]
        },
        {
            name: 'links',
            items: [
                'Link',
                'Unlink'
            ]
        },
    ],
    fillEmptyBlocks: false,
    autoParagraph: false
} );
var el = wp.element.createElement,
  registerBlockType = wp.blocks.registerBlockType,
  blockStyle = { backgroundColor: '#900', color: '#fff', padding: '20px'};
  RichText = wp.editor.RichText;
  BlockControls = wp.editor.BlockControls,
  AlignmentToolbar = wp.editor.AlignmentToolbar;

  registerBlockType('gutenberg-boilerplate-es5/hello-world-step-01', {
    title: 'Hello World (Step 1)',
    icon: 'universal-access-alt',
    category: 'layout',
    attributes: {
        content: {
            type: 'array',
            source: 'children',
            selector: 'p',
        }
    },

    edit: function( props ) {
        var content = props.attributes.content;

        function onChangeContent( newContent ) {
            props.setAttributes( { content: newContent } );
        }

        return el(
            RichText,
            {
                tagName: 'p',
                className: props.className,
                onChange: onChangeContent,
                value: content,
            }
        );
    },

    save: function( props ) {
        var content = props.attributes.content;

        return el( RichText.Content, {
            tagName: 'p',
            className: props.className,
            value: content
        } );
    },
  });

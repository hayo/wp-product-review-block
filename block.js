var el = wp.element.createElement;
var registerBlockType = wp.blocks.registerBlockType;
var RichText = wp.blockEditor.RichText;
var InnerBlocks = wp.blockEditor.InnerBlocks;
var MediaUpload = wp.blockEditor.MediaUpload;
var URLInput = wp.blockEditor.URLInput;
var InspectorControls = wp.blockEditor.InspectorControls;
var PanelBody = wp.components.PanelBody;

registerBlockType('custom/product-review-block', {
    title: 'Product Review Block',
    icon: 'index-card',
    category: 'common',
    attributes: {
        productName: {
            type: 'string',
            default: 'Product Name',
        },
        manufacturerName: {
            type: 'string',
            default: 'Manufacturer Name',
        },
        productURL: {
            type: 'string',
            default: 'https://www.example.com/product-page',
        },
        productImage: {
            type: 'string',
            default: '', // Default image URL
        },
        ratingValue: {
            type: 'number',
            default: 4, // Default rating
        },
        reviewBody: {
            type: 'string',
            default: '',
        },
    },
    edit: function (props) {
        var attributes = props.attributes;
        var setAttributes = props.setAttributes;

        function onSelectImage(media) {
            if (media && media.url) {
                setAttributes({ productImage: media.url });
            }
        }

        function onRemoveImage() {
            setAttributes({ productImage: '' });
        }        

        function onChangeProductName(newProductName) {
            setAttributes({ productName: newProductName });
        }

        function onChangeManufacturerName(newManufacturerName) {
            setAttributes({ manufacturerName: newManufacturerName });
        }

        function onChangeProductURL(newProductURL) {
            setAttributes({ productURL: newProductURL });
        }

        function onChangeRatingValue(newRatingValue) {
            setAttributes({ ratingValue: newRatingValue });
        }

        function onChangeReviewBody(newReviewBody) {
            setAttributes({ reviewBody: newReviewBody });
        }

        // Function to handle image selection
        function onSelectImage(media) {
            if (media && media.url) {
                setAttributes({ productImage: media.url });
            }
        }

        return [
            el('div', { className: props.className },
                el(InspectorControls, { key: 'inspector' },
                    el(PanelBody, {
                            title: 'Product Options',
                            initialOpen: true
                        },
                        // Image Selection
                        el('label', { className: 'components-base-control__label product-option-label' }, 'Product Image'),
                        el(MediaUpload, {
                            onSelect: onSelectImage,
                            allowedTypes: ['image'],
                            value: attributes.productImage,
                            render: function (r) {
                                const { open } = r;
                                return el('div', {},
                                    el('button', {
                                        onClick: open,
                                        className: 'components-button components-icon-button',
                                    }, attributes.productImage ?
                                        el('img', {
                                            src: attributes.productImage,
                                            alt: 'Product'
                                        }) :
                                        'Choose Image'
                                    ),
                                    attributes.productImage && el('button', {
                                        onClick: onRemoveImage,
                                        className: 'components-button components-icon-button',
                                    }, 'Remove Image')
                                );
                            },
                        }),

                        // Product URL
                        el(URLInput, {
                            value: attributes.productURL,
                            onChange: onChangeProductURL,
                            label: 'Product URL',
                        }),

                        // Manufacturer Name
                        el('label', { className: 'components-base-control__label product-option-label' }, 'Manufacturer Name'),
                        el('input', {
                            type: 'text',
                            value: attributes.manufacturerName,
                            onChange: function (event) {
                                onChangeManufacturerName(event.target.value);
                            }
                        }),

                        // Rating Value
                        el('label', { className: 'components-base-control__label product-option-label' }, 'Rating Value'),
                        el('input', {
                            type: 'number',
                            value: attributes.ratingValue,
                            onChange: function (e) {
                                onChangeRatingValue(e.target.value);
                            },
                        }),
                    ),
                ),

                // Product Name RichText
                el('div', { className: 'components-base-control product-option' },
                    el(RichText, {
                        tagName: 'h2',
                        value: attributes.productName,
                        onChange: onChangeProductName,
                        placeholder: 'Product Name',
                        inline: false,
                    }),
                ),

                // Review Body
                el('div', { className: 'review-body' },
                    el(InnerBlocks, {
                        allowedBlocks: ['core/paragraph'],
                        template: [['core/paragraph']],
                        templateLock: false,
                        __experimentalMerged: true,
                        onChange: onChangeReviewBody,
                    }),
                ),
            ),
        ];
    },

    save: function(props) {
        var attributes = props.attributes;

        return (
            el('div', {},
                el('div', {
                        itemprop: 'itemReviewed',
                        itemscope: true,
                        itemtype: 'http://schema.org/Product',
                    },
                    el('h2', { itemprop: 'name' }, attributes.productName),
                    el('div', {
                            itemprop: 'manufacturer',
                            itemscope: true,
                            itemtype: 'http://schema.org/Organization',
                        },
                        'Manufacturer: ',
                        el('span', { itemprop: 'name' }, attributes.manufacturerName)
                    ),
                    el('strong', {}, 'Link to Product:'),
                    el('a', {
                        href: attributes.productURL,
                        itemprop: 'url',
                    }, 'Product Page'),
                    el('img', {
                        itemprop: 'image',
                        src: attributes.productImage,
                        alt: 'Product image',
                    }),
                    el('div', {
                            itemprop: 'reviewRating',
                            itemscope: true,
                            itemtype: 'http://schema.org/Rating',
                        },
                        el('meta', {
                            itemprop: 'ratingValue',
                            content: attributes.ratingValue,
                        })
                    ),
                    el('div', {
                            itemprop: 'reviewBody',
                            className: 'review-body',
                        },
                        el(InnerBlocks.Content)
                    )
                )
            )
        );
    },
});

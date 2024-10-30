/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-i18n/
 */
import {__} from '@wordpress/i18n';


/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-block-editor/#useblockprops
 */
import {InspectorControls, useBlockProps} from '@wordpress/block-editor';

/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * Those files can contain any CSS code that gets applied to the editor.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#edit
 *
 * @return {WPElement} Element to render.
 */

import {Panel, PanelBody, PanelRow, CheckboxControl, TextControl, ColorPalette, InputControl} from '@wordpress/components'

export default function Edit({attributes: {content, textColor,widgetID}, setAttributes}) {

	function editContentHandler(newVal) {
		setAttributes({content: newVal});
	}

	return (
		<>
			<InspectorControls>
				<panelBody title="Widget Settings">
					<TextControl
						label="Widget ID"
						value={ widgetID }
						onChange={ ( value ) => setAttributes( {widgetID : value} ) }
						placeholder="Widget ID supplied by CognativeX"
					/>

				</panelBody>
			</InspectorControls>

			<div {...useBlockProps({id:{widgetID}})}   >
				This Block will be replaced by CX Widget with id = {widgetID}
			</div>
		</>

	);
}

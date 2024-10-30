/*!
 * bsCustomFileInput v1.3.1 (https://github.com/Johann-S/bs-custom-file-input)
 * Copyright 2018 Johann-S <johann.servoire@gmail.com>
 * Licensed under MIT (https://github.com/Johann-S/bs-custom-file-input/blob/master/LICENSE)
 */
!function(t,e){"object"==typeof exports&&"undefined"!=typeof module?module.exports=e():"function"==typeof define&&define.amd?define(e):t.bsCustomFileInput=e()}(this,function(){"use strict";var f={CUSTOMFILE:'.custom-file input[type="file"]',CUSTOMFILELABEL:".custom-file-label",FORM:"form",INPUT:"input"},o=function(t){if(0<t.childNodes.length)for(var e=[].slice.call(t.childNodes),n=0;n<e.length;n++){var o=e[n];if(3!==o.nodeType)return o}return t},a=function(t){var e=t.bsCustomFileInput.defaultText,n=t.parentNode.querySelector(f.CUSTOMFILELABEL);n&&(o(n).innerHTML=e)},n=!!window.File,i=function(t){if(t.hasAttribute("multiple")&&n)return[].slice.call(t.files).map(function(t){return t.name}).join(", ");if(-1===t.value.indexOf("fakepath"))return t.value;var e=t.value.split("\\");return e[e.length-1]};function h(){var t=this.parentNode.querySelector(f.CUSTOMFILELABEL);if(t){var e=o(t),n=i(this);n.length?e.innerHTML=n:a(this)}}function d(){for(var t=[].slice.call(this.querySelectorAll(f.INPUT)).filter(function(t){return!!t.bsCustomFileInput}),e=0,n=t.length;e<n;e++)a(t[e])}var p="bsCustomFileInput",y="reset",m="change";return{init:function(t,e){void 0===t&&(t=f.CUSTOMFILE),void 0===e&&(e=f.FORM);for(var n,o,i,r=[].slice.call(document.querySelectorAll(t)),A=[].slice.call(document.querySelectorAll(e)),a=0,l=r.length;a<l;a++){var c=r[a];Object.defineProperty(c,p,{value:{defaultText:(n=c,o=void 0,void 0,o="",i=n.parentNode.querySelector(f.CUSTOMFILELABEL),i&&(o=i.innerHTML),o)},writable:!0}),c.addEventListener(m,h)}for(var s=0,u=A.length;s<u;s++)A[s].addEventListener(y,d),Object.defineProperty(A[s],p,{value:!0,writable:!0})},destroy:function(){for(var t=[].slice.call(document.querySelectorAll(f.FORM)).filter(function(t){return!!t.bsCustomFileInput}),e=[].slice.call(document.querySelectorAll(f.INPUT)).filter(function(t){return!!t.bsCustomFileInput}),n=0,o=e.length;n<o;n++){var i=e[n];a(i),i[p]=void 0,i.removeEventListener(m,h)}for(var r=0,A=t.length;r<A;r++)t[r].removeEventListener(y,d),t[r][p]=void 0}}});

jQuery(document).ready(function($)
{
	bsCustomFileInput.init();
	var clone	= $('button.form__add-file').parent().parent().find('div.input-group').first().clone();

/*	clone.clone().insertAfter($('button.form__add-file').parent().parent().find('div.input-group:last-child'));
	clone.clone().insertAfter($('button.form__add-file').parent().parent().find('div.input-group:last-child'));*/

	bsCustomFileInput.init()



	$(document).on('click', 'button.form__add-file', function(e)
	{
		e.preventDefault();
		clone.clone().insertAfter($(this).parent().parent().find('div.input-group:last-child'));
		bsCustomFileInput.init()
	});

	document.addEventListener( 'wpcf7mailsent', function( event )
	{
		$('button.form__add-file').parent().parent().find('div.input-group').not(':last-child').remove();
		clone.clone().insertAfter($('button.form__add-file').parent().parent().find('div.input-group:last-child'));
		clone.clone().insertAfter($('button.form__add-file').parent().parent().find('div.input-group:last-child'));

		jQuery('form.wpcf7-form').trigger('reset');

		bsCustomFileInput.init()

	}, false );
});
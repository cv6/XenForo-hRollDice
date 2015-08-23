/** @param {jQuery} $ jQuery Object */
!function($, window, document, _undefined)
{	
	XenForo.SortableList = function($list)
	{
		$list.sortable();
	}

	XenForo.changeDieBackground = function($form)
	{
		// ctrl_image
		var $img = $('input#ctrl_image');
		if ($img.val() !== "")
			$form.css('background-image','url('+$img.val()+')');
		$img.blur(function() {
			var imgurl = $(this).val();
			if (imgurl !== "")
				$form.css('background-image','url('+imgurl+')');
			else
				$form.css('background-image','url(styles/default/hoffi/dice/default.png)');
				
		});
	}


	XenForo.changeWsBackground = function($form)
	{
		// ctrl_image
		var $img = $('input#ctrl_image');
		if ($img.val() !== "")
			$form.css('background-image','url('+$img.val()+')');
		$img.blur(function() {
			var imgurl = $(this).val();
			if (imgurl !== "")
				$form.css('background-image','url('+imgurl+')');
			else
				$form.css('background-image','none');
				
		});
	}
	
	// *********************************************************************

	XenForo.register('#WiresetList li', 'XenForo.SortableList');
	XenForo.register('form.DiePreview', 'XenForo.changeDieBackground');
	XenForo.register('form.WsPreview', 'XenForo.changeWsBackground');
	

}
(jQuery, this, document);

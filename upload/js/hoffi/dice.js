/** @param {jQuery} $ jQuery Object */
!function($, window, document, _undefined)
{

	XenForo.RollDice = function($form)
	{
		$form.bind('AutoValidationComplete', function(e)
		{
			if (e.ajaxData)
			{
				if (e.ajaxData.feedback)
				{
						$('#notRolledYet').slideUp(XenForo.speed.fast, function() {
							$('.RolledDiceInfo').slideDown();
							var dice = '';
							$.each(e.ajaxData.feedback.dice, function(k,v) {
								if (dice !== "") dice += ", ";
								dice += v + ' x ' + k;
							});
							$('<li>').text(e.ajaxData.feedback.wireset.title+': '+dice)
											.xfInsert('appendTo', 'ul#nowRolled');
					});
				}
				$('div[id|=ws]').slideUp();
			}
		});
	};

	XenForo.DeleteRoll = function($form)
	{
		$form.bind('AutoValidationComplete', function(e)
		{
			if (e.ajaxData)
			{
				console.log(e.ajaxData);
				if (e.ajaxData.roll_id)
				{
					$('li#rollcontainer-'+e.ajaxData.roll_id).slideUp();
				}
			}
		});
	};


	XenForo.ChooseWireset = function($link) {
		$link.click(function (e) {
			var id = $(this).attr('id').split('-')[1];
			$('#dicetag').val(id);
		});
	};

	XenForo.OpenRoll = function($icon) {
		$icon.click(function(e) {
			if ($icon.hasClass('open'))
			{
				$icon.removeClass('open');
				$($icon.data('open')).slideUp(XenForo.speed.fast);
			}
			else
			{
				$icon.addClass('open');
				$($icon.data('open')).slideDown(XenForo.speed.fast);
			}
		});
	};

	XenForo.ClearRolls = function($form) {
		$form.bind('AutoValidationComplete', function(e)
		{
			$('.RolledDiceInfo').slideUp(XenForo.speed.fast, function() {
				$('#notRolledYet').slideDown(XenForo.speed.fast);
				$('ul#nowRolled li').remove();
			});
		});
	};

	// *********************************************************************

	XenForo.register('form#RollDice', 'XenForo.RollDice');
	XenForo.register('form#DeleteRoll', 'XenForo.DeleteRoll');
  XenForo.register('#diceTabs li a', 'XenForo.ChooseWireset');
  XenForo.register('span.OpenRoll', 'XenForo.OpenRoll');
  XenForo.register('form#QuickReply', 'XenForo.ClearRolls');

}
(jQuery, this, document);

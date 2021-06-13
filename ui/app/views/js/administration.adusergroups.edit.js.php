<?php
/**
 * @var CView $this
 */
?>

<script type="text/javascript">
	jQuery(function($) {
		let $form = $('form[name="ad_group_form"]').submit(function() {
			$(this).trimValues(['#adgname']);
		});

		$('#roleid').change(function() {
			if ($(this).find('[name=roleid]').length) {
				$form.submit();
			}
		});
	});
</script>

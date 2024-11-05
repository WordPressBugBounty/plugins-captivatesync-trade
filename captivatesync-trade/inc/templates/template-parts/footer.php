<?php
/**
 * Footer template
 */
?>

<div class="cfm-page-footer">

	<div class="footer-content">
		<div class="footer-left">
			<p><strong>&copy; <?php echo date( 'Y' ); ?> Captivate Audio Ltd</strong></p>
			<p class="made-with-love"> Made with <i class="fas fa-heart"></i> in the UK </p>
		</div>

		<div class="footer-right">
			<p>
				<a href="https://captivate.fm/privacy" target="_blank">Privacy Policy</a>
				<a href="https://captivate.fm/cookie-policy" target="_blank">Cookie Policy</a>
				<a href="https://captivate.fm/terms" target="_blank">Terms &amp; Conditions</a>
				<a href="https://help.captivate.fm/en/articles/3045914-captivate-feature-releases-changelog" target="_blank">Changelog</a>
				<a href="https://status.captivate.fm" target="_blank">System Status</a>
				<a href="https://affiliate.captivate.fm" target="_blank">Affiliate Portal</a>
				<a href="https://help.captivate.fm/en/?q=sync" target="_blank">CaptivateSync&trade; Help</a>
			</p>
		</div>
	</div>

	<div id="confirmation-modal" class="modal fade confirmation-modal" data-bs-backdrop="static" data-bs-keyboard="false" aria-labelledby="confirm-changes" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered" role="document">
			<div class="modal-content">
				<div class="modal-header"><h4 class="modal-title"></h4></div>

				<div class="modal-body"><p class="fw-light"></p></div>

				<div class="modal-footer">
					<button type="button" class="btn btn-outline-primary me-auto" data-bs-dismiss="modal">Cancel</button>
					<button type="button" class="btn btn-primary modal-confirm">Confirm</button>
				</div>
			</div>
		</div>
	</div>

</div>

<!-- Podcast settings modal -->
<div id="cfm-show-settings-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title">Podcasts Settings</h4>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>

			<div class="modal-body">
				<div id="cfm-show-settings" class="cfm-show-settings"></div>
			</div>

			<div class="modal-footer">
				<button type="button" class="btn btn-outline-primary me-auto" data-bs-dismiss="modal">Close</button>
				<button id="cfm-save-show-settings" type="button" class="btn btn-primary">Save Podcast Settings</button>
			</div>
		</div>
	</div>
</div>
<!-- /Podcast settings modal -->

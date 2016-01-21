		<footer>

		</footer>
		<div class="modal-holder" id="ajax-modal">
			<div class="modal-wrap">
				<div class="modal">
					<i id="modal_close" class="close fa fa-times" onclick="hideModal('ajax-modal');"></i>
					<h1 id="modal_h1"></h1>
					<div id="modal_content"></div>
				</div>
			</div>
		</div>
		<ul id="top_error" onclick="closeTopError()"></ul>
		<div id="blackout" class="blackout"></div>
		<div id="fullscreenload" class="fullscreenload"><span></span><img src="/img/loading.gif"></div>
		<script> $(document).keydown(function(e) {
			if (e.keyCode == 27) { hideModal(document.openModal);
			} });
		</script>
		<script type="text/javascript" src="/js/ckeditor/ckeditor.js"></script>
	</body>
</html>

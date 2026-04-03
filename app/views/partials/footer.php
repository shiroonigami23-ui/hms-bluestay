  <footer class="site-footer">
    <div>
      <strong>BlueStay HMS</strong> &copy; <?= date('Y') ?>
    </div>
    <div class="footer-links">
      <a href="terms.php">Terms</a>
      <a href="privacy.php">Privacy</a>
    </div>
  </footer>
</div>
<div class="dialog-backdrop" id="appDialogBackdrop" hidden>
  <div class="dialog-box" role="dialog" aria-modal="true">
    <h3 id="appDialogTitle">Notice</h3>
    <p id="appDialogMessage">Message</p>
    <input id="appDialogInput" class="dialog-input" type="text" hidden>
    <div class="dialog-actions">
      <button class="btn btn-ghost" id="appDialogCancel" type="button">Cancel</button>
      <button class="btn" id="appDialogOk" type="button">OK</button>
    </div>
  </div>
</div>
<script src="assets/js/app.js"></script>
</body>
</html>

<h3>Captcha demo</h3>
<?php if (isset($response)) echo "<p>$response</p>"; ?>
<form method="post">
<img src="<?php echo $this->config->site_url('captcha'); ?>" alt="captcha" /><br/>
Captcha: <input autocomplete="off" name="captcha_input" size="50" placeholder="type exactly what you see in image" />
<input type="submit" />
</form>
<script type="text/hmtl" id="tmpl-<?=$params['template_login_register_form_id']?>">
	 <form id="simple-default-login-form" method="post" action="{{data.url}}">
	  <div class="simple-default-container">
	    <p><h1>Simple Login Register</h1></p>
	    <p>{{{data.response.form_title}}}</p>
	    <# if ( data.response.notice ) { #>
	    <p>{{{data.l10n[data.response.notice]}}}</p>
	    <# } #>	    
	    <hr>
		<input type="hidden" name="{{data.field_nonce}}" value="{{{data.response[data.field_nonce]}}}">
		<input type="hidden" name="{{data.field_http_referer}}" value="{{data.http_referer}}">
		<# if ( data.response[data.field_email] ) { #>
		<input type="hidden" name="{{data.field_email}}" value="{{data.response[data.field_email]}}">
		<# } #>
		<# if ( data.response.form_title && (data.l10n.signup_title == data.response.form_title) ) { #>		
		<div>
		    <label for="{{data.field_name}}"><b>{{{data.l10n.fullname}}}</b></label>
		    <input type="text" placeholder="{{data.l10n.enter_fullname}}" id="{{data.field_name}}" name="{{data.field_name}}" value="{{data.response[data.field_name]}}" required">	
	    </div>		
	    <# } #>
		<# if ( data.response.form_title && ((data.l10n.login_title == data.response.form_title) || (data.l10n.signup_title == data.response.form_title)) ) { #>	    
	    <div>
		    <label for="{{data.field_email}}"><b>{{{data.l10n.email}}}</b></label>
		    <input type="text" id="{{data.field_email}}" placeholder="{{data.l10n.enter_email}}" name="{{data.field_email}}" value="{{data.response[data.field_email]}}" required>
		</div>
		<# } #>
		<# if ( data.response.form_title && ((data.l10n.login_password_title == data.response.form_title) || (data.l10n.signup_title == data.response.form_title)) ) { #>
		<div>
		    <label for="{{data.field_password}}"><b>{{{data.l10n.password}}}</b></label>
		    <input type="password" id="{{data.field_password}}" placeholder="{{data.l10n.enter_password}}" name="{{data.field_password}}" value="{{data.response[data.field_password]}}" required>
		</div>
		<# } #>
		<# if ( data.response.form_title && (data.l10n.signup_title == data.response.form_title) ) { #>			
		<div>
		    <label for="{{data.field_repassword}}"><b>{{{data.l10n.repeat_password}}}</b></label>
		    <input type="password" id="{{data.field_repassword}}" placeholder="{{data.l10n.repeat_password}}" name="{{data.field_repassword}}" value="{{data.response[data.field_repassword]}}">
	    </div>	
	    <# } #>	
	    <div class="clearfix">
	      <button type="submit" class="continue">{{{data.l10n.continue}}}</button>
	    </div>
	  </div>
	</form>
</script>

<script type="text/html" id="tmpl-<?=$params['template_dashboard_id']?>">
	<div>{{{data.l10n.dashboard}}}</div>
</script>
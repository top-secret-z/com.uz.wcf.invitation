{include file='userMenuSidebar'}

{capture assign='pageTitle'}{lang}wcf.user.invite.add{/lang}{/capture}

{capture assign='contentTitle'}{lang}wcf.user.invite.add{/lang}{/capture}

{capture assign='contentDescription'}{$description}{if INVITE_CODE_OPTION == 'may' && INVITE_CODE_USERNAME}<br>{lang}wcf.user.invite.description.username{/lang}{/if}{/capture}

{include file='header' __disableAds=true __sidebarLeftHasMenu=true}

{include file='formError'}

{if $success|isset}
	<p class="success">{lang}wcf.global.success.edit{/lang}</p>
{/if}

<form method="post" action="{link controller='InviteAdd'}{/link}">
	<div id="InviteAdd">
		{if $code}
			<section class="section">
				<header class="sectionHeader">
					<h2 class="sectionTitle">{lang}wcf.user.invite.code{/lang}</h2>
					<p class="sectionDescription">{lang}wcf.user.invite.code.description{/lang}</p>
				</header>
				
				<dl>
					<dt></dt>
					<dd>
						<strong>{$code}</strong>
						<input type="text" id="code" name="code" value="{$code}" class="long" hidden>
						<small>{lang}wcf.user.invite.code.validity{/lang}</small>
					</dd>
				</dl>
				
				<dl>
					<dt><label for="method">{lang}wcf.user.invite.method{/lang}</label></dt>
					<dd class="floated">
						<label><input type="radio" name="method" value="copy"{if $method == 'copy'} checked{/if}> {lang}wcf.user.invite.method.copy{/lang}</label>
						<label><input type="radio" name="method" value="email"{if $method == 'email'} checked{/if}> {lang}wcf.user.invite.method.email{/lang}</label>
					</dd>
				</dl>
			</section>
		{/if}
		
		<section id="emailSection" class="section">
			<header class="sectionHeader">
				<h2 class="sectionTitle">{lang}wcf.user.invite.email{/lang}</h2>
				<p class="sectionDescription">{lang}wcf.user.invite.email.description{/lang}</p>
			</header>
			
			<dl{if $errorField == 'emailField'} class="formError"{/if}>
				<dt><label for="emailField">{lang}wcf.user.invite.emailField{/lang}</label></dt>
				<dd>
					<textarea name="emailField" id="emailField" rows="3">{$emailField}</textarea>
					<small>{lang}wcf.user.invite.emailField.description{/lang}</small>
					
					{if $errorField == 'emailField'}
						<small class="innerError">
							{if $errorType == 'empty'}
								{lang}wcf.global.form.error.empty{/lang}
							{else}
								{lang}wcf.user.invite.emailField.error.{@$errorType}{/lang}
							{/if}
						</small>
					{/if}
				</dd>
			</dl>
			
			<dl{if $errorField == 'subject'} class="formError"{/if}>
				<dt><label for="subject">{lang}wcf.user.invite.subject{/lang}</label></dt>
				<dd>
					<input type="text" id="subject" name="subject" value="{$subject}" class="long">
					
					{if $errorField == 'subject'}
						<small class="innerError">
							{if $errorType == 'empty'}
								{lang}wcf.global.form.error.empty{/lang}
							{else}
								{lang}wcf.user.invite.subject.error.{@$errorType}{/lang}
							{/if}
						</small>
					{/if}
				</dd>
			</dl>
			
			<dl{if $errorField == 'message'} class="formError"{/if}>
				<dt><label for="message">{lang}wcf.user.invite.message{/lang}</label></dt>
				<dd>
					<textarea name="message" id="message" rows="6">{$message}</textarea>
					
					{if $errorField == 'message'}
						<small class="innerError">
							{if $errorType == 'empty'}
								{lang}wcf.global.form.error.empty{/lang}
							{else}
								{lang}wcf.user.invite.message.error.{@$errorType}{/lang}
							{/if}
						</small>
					{/if}
				</dd>
			</dl>
		</section>
		
		{event name='sections'}
	</div>
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s">
		{csrfToken}
	</div>
</form>

<script data-relocate="true">
	$(function() {
		$('input[type="radio"][name="method"]').change(function(event) {
			var $selected = $('input[type="radio"][name="method"]:checked');
			if ($selected.length > 0) {
				if ($selected.val() == 'email') {
					$('#emailSection').show();
				}
				else {
					$('#emailSection').hide();
				}
			}
		}).trigger('change');
	});
</script>

{include file='footer'}

{if MODULE_INVITE && INVITE_DISPLAY_USERINFORMATION && $user->invites}
	<dt><a href="{link controller='InviteAdd'}{/link}" class="jsTooltip" title="{lang}wcf.user.invite.add{/lang}">{lang}wcf.user.invite.invites{/lang}</a></dt>
	<dd>{#$user->invites} {if $user->inviteSuccess}({#$user->inviteSuccess}){/if}</dd>
{/if}
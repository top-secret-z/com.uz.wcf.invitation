{include file='header' pageTitle='wcf.acp.invite.list'}

<script data-relocate="true">
	//<![CDATA[
	$(function() {
		new WCF.Action.Delete('wcf\\data\\user\\invite\\InviteAction', '.jsInviteRow');
		
		var options = { };
		{if $pages > 1}
			options.refreshPage = true;
			{if $pages == $pageNo}
				options.updatePageNumber = -1;
			{/if}
		{else}
			options.emptyMessage = '{lang}wcf.global.noItems{/lang}';
		{/if}
		
		new WCF.Table.EmptyTableHandler($('#inviteTableContainer'), 'jsInviteRow', options);
	});
	//]]>
</script>

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.invite.list{/lang}</h1>
	</div>
	
	{hascontent}
		<nav class="contentHeaderNavigation">
			<ul>
				{content}
				
					{event name='contentHeaderNavigation'}
				{/content}
			</ul>
		</nav>
	{/hascontent}
</header>

{hascontent}
	<div class="paginationTop">
		{content}{pages print=true assign=pagesLinks controller="InviteList" link="pageNo=%d&sortField=$sortField&sortOrder=$sortOrder"}{/content}
	</div>
{/hascontent}

{if $objects|count}
	<div class="section tabularBox" id="inviteTableContainer">
		<table class="table">
			<thead>
				<tr>
					<th class="columnID columnInviteID{if $sortField == 'inviteID'} active {@$sortOrder}{/if}" colspan="2"><a href="{link controller='InviteList'}pageNo={@$pageNo}&sortField=inviteID&sortOrder={if $sortField == 'inviteID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.global.objectID{/lang}</a></th>
					<th class="columnText columnCode{if $sortField == 'code'} active {@$sortOrder}{/if}"><a href="{link controller='InviteList'}pageNo={@$pageNo}&sortField=code&sortOrder={if $sortField == 'code' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.invite.code{/lang}</a></th>
					<th class="columnDate columnTime{if $sortField == 'time'} active {@$sortOrder}{/if}"><a href="{link controller='InviteList'}pageNo={@$pageNo}&sortField=time&sortOrder={if $sortField == 'time' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.invite.time{/lang}</a></th>
					<th class="columnText columnInviterName{if $sortField == 'inviterName'} active {@$sortOrder}{/if}"><a href="{link controller='InviteList'}pageNo={@$pageNo}&sortField=inviterName&sortOrder={if $sortField == 'inviterName' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.invite.inviterName{/lang}</a></th>
					<th class="columnDigits columnSuccessCount{if $sortField == 'successCount'} active {@$sortOrder}{/if}"><a href="{link controller='InviteList'}pageNo={@$pageNo}&sortField=successCount&sortOrder={if $sortField == 'successCount' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.invite.successCount{/lang}</a></th>
					<th class="columnText columnEmails{if $sortField == 'emails'} active {@$sortOrder}{/if}"><a href="{link controller='InviteList'}pageNo={@$pageNo}&sortField=emails&sortOrder={if $sortField == 'emails' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.invite.emails{/lang}</a></th>
					<th class="columnText columnText{if $sortField == 'message'} active {@$sortOrder}{/if}"><a href="{link controller='InviteList'}pageNo={@$pageNo}&sortField=message&sortOrder={if $sortField == 'message' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.invite.message{/lang}</a></th>
					
					{event name='columnHeads'}
				</tr>
			</thead>
			
			<tbody>
				{foreach from=$objects item=invite}
					<tr class="jsInviteRow">
						<td class="columnIcon">
							<span class="icon icon16 fa-times jsDeleteButton jsTooltip pointer" title="{lang}wcf.global.button.delete{/lang}" data-object-id="{@$invite->inviteID}" data-confirm-message-html="{lang __encode=true}wcf.acp.invite.delete.sure{/lang}"></span>
							
							{event name='rowButtons'}
						</td>
						<td class="columnID columnInviteID">{@$invite->inviteID}</td>
						<td class="columnText columnCode">{$invite->code}</td>
						<td class="columnDate columnTime">{@$invite->time|time}</td>
						<td class="columnText columnInviterName">{$invite->inviterName}</td>
						<td class="columnDigits columnSuccessCount">{#$invite->successCount}</td>
						<td class="columnText columnEmails">{$invite->emails}</td>
						<td class="columnText columnText">{$invite->message}</td>
						
						{event name='columns'}
					</tr>
				{/foreach}
			</tbody>
		</table>
	</div>
{else}
	<p class="info">{lang}wcf.global.noItems{/lang}</p>
{/if}

<footer class="contentFooter">
	{hascontent}
		<div class="paginationBottom">
			{content}{@$pagesLinks}{/content}
		</div>
	{/hascontent}
	
	{hascontent}
		<nav class="contentFooterNavigation">
			<ul>
				{content}
				
					{event name='contentFooterNavigation'}
				{/content}
			</ul>
		</nav>
	{/hascontent}
</footer>

{include file='footer'}

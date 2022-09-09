{include file='userMenuSidebar'}

{capture assign='contentTitleBadge'}<span class="badge">{#$items}</span>{/capture}

{capture assign='pageTitle'}{lang}wcf.user.invite.myInvites{/lang}{/capture}

{capture assign='contentHeader'}
	{assign var="count" value=$__wcf->getUser()->inviteSuccess}
	<header class="contentHeader">
		<div class="contentHeaderTitle">
			<h1 class="contentTitle">{lang}wcf.user.invite.myInvites{/lang} <span class="badge">{#$__wcf->getUser()->invites}</span></h1>
			{if $count}
				<p>{lang}wcf.user.invite.myInviteSuccess{/lang}</p>
			{/if}
		</div>
	</header>
{/capture}

{if !WCF_VERSION|substr:0:3 >= '5.5'}
	{capture assign='contentInteractionPagination'}
		{pages print=true assign=pagesLinks controller='InviteListUser' link="pageNo=%d"}
	{/capture}
	
	{include file='header' __sidebarLeftHasMenu=true}
{else}
	{include file='header' __sidebarLeftHasMenu=true}
	
	{hascontent}
		<div class="paginationTop">
			{content}{pages print=true assign=pagesLinks controller='InviteListUser' link="pageNo=%d"}{/content}
		</div>
	{/hascontent}
{/if}

{if !$usernames|empty}
	<div class="section">
		<ol class="containerList">
			<li>
				<div class="containerHeadline">
					<h3>{lang}wcf.user.invite.myInvites.username{/lang}</h3>
					{@$usernames}
				</div>
			</li>
		</ol>
	</div>
{/if}

{if $objects|count}
	<div class="section">
		<ol class="containerList">
			{foreach from=$objects item=invite}
				<li>
					<div class="containerHeadline">
						{if $invite->code}
							<h3>{$invite->code}{if $invite->subject} <span><small>{$invite->subject|truncate:45}</small></span>{/if}</h3>
						{else}
							<h3>{$invite->subject|truncate:45}</h3>
						{/if}
						<ul class="inlineList">
							<li>
								<span class="icon icon16 fa-clock-o"></span>
								{@$invite->time|time}
							</li>
							
							<li>
								<span class="icon icon16 fa-user"></span>
								{lang}wcf.user.invite.success{/lang}
							</li>
							
							<li>
								<span class="icon icon16 fa-envelope"></span>
								{if $invite->emails}
									{$invite->emails}
								{else}
									{lang}wcf.user.invite.code.share{/lang}
								{/if}
							</li>
						</ul>
						
						<ul class="inlineList">
							<li>{@$invite->getUsernames()}</li>
						</ul>
						
						{if $invite->message}
							<span><small>{$invite->getExcerpt()}</small></span>
						{/if}
					</div>
				</li>
			{/foreach}
		</ol>
	</div>
	
	<footer class="contentFooter">
		{hascontent}
			<div class="paginationBottom">
				{content}{@$pagesLinks}{/content}
			</div>
		{/hascontent}
		
		{hascontent}
			<nav class="contentFooterNavigation">
				<ul>
					{content}{event name='contentFooterNavigation'}{/content}
				</ul>
			</nav>
		{/hascontent}
	</footer>
{else}
	<p class="info">{lang}wcf.user.invite.noInvites{/lang}</p>
{/if}

{include file='footer'}

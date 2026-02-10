@props(['totalCount', 'onlineCount'])

<p class="stats-bar-text">
    {{ number_format($totalCount) }} models total
    <span style="margin: 0 0.5rem;">•</span>
    <span class="stats-bar-online">{{ number_format($onlineCount) }} online now</span>
</p>

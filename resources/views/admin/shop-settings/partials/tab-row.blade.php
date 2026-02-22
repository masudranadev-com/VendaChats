<section class="settings-tab-row">
  @foreach ($shopTabs as $tab)
    <a href="{{ route($tab['route']) }}" class="settings-tab {{ $activeTab === $tab['key'] ? 'active' : '' }}">
      {{ $tab['label'] }}
    </a>
  @endforeach
</section>

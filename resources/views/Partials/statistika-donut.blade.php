@php
  $statTahunOptions ??= collect();
  $statUnitOptions  ??= collect();
@endphp

<div class="stat-card">
  <div class="stat-head">
    <div class="stat-title">{{ $title }}</div>
  </div>

  <div class="stat-filters">
    <select class="stat-sel" id="donutYear">
      <option value="" selected>Semua Tahun</option>
      @foreach($statTahunOptions as $t)
        <option value="{{ $t }}">{{ $t }}</option>
      @endforeach
    </select>

    <select class="stat-sel" id="donutUnit">
      <option value="" selected>Semua Unit</option>
      @foreach($statUnitOptions as $u)
        <option value="{{ $u->id }}">{{ $u->nama }}</option>
      @endforeach
    </select>
  </div>

  <div class="stat-body stat-body--donut">
    <div class="stat-canvas stat-canvas--donut">
      <canvas id="{{ $donutId }}"></canvas>
    </div>
  </div>
</div>
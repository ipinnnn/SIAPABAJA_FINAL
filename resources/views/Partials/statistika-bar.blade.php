@php
  $statTahunOptions ??= collect();
  $statUnitOptions  ??= collect();
@endphp

<div class="stat-card">
  <div class="stat-head">
    <div class="stat-title">{{ $title }}</div>
  </div>

  <div class="stat-filters">
    <select class="stat-sel" id="barYear">
      <option value="" selected>Semua Tahun</option>
      @foreach($statTahunOptions as $t)
        <option value="{{ $t }}">{{ $t }}</option>
      @endforeach
    </select>

    <select class="stat-sel" id="barUnit">
      <option value="" selected>Semua Unit</option>
      @foreach($statUnitOptions as $u)
        <option value="{{ $u->id }}">{{ $u->nama }}</option>
      @endforeach
    </select>
  </div>

  <div class="stat-body stat-body--bar">
    <div class="stat-canvas stat-canvas--bar">
      <canvas id="{{ $barId }}"></canvas>
    </div>
  </div>
</div>
@extends('admin.master')

@section('title', $title)

@section('admin.content')
  <div class="page-header competition-page-header">
    <div>
      <h1 class="page-title">{{ $title }}</h1>
      <p class="page-subtitle">{{ $subtitle }}</p>
    </div>

    <div class="competition-header-actions">
      <a href="{{ route('admin.competition') }}" class="btn btn-secondary">Back to Table</a>
      <form method="POST" action="{{ route('admin.competition.sync', $competitor['id']) }}">
        @csrf
        <button type="submit" class="btn btn-primary">Sync Now</button>
      </form>
    </div>
  </div>

  <section class="competition-metrics">
    @foreach ($report['summary_cards'] as $card)
      <article class="competition-metric-card">
        <span>{{ $card['label'] }}</span>
        <strong>{{ $card['value'] }}</strong>
        <small>{{ $card['note'] }}</small>
      </article>
    @endforeach
  </section>

  <div class="competition-layout mt-xl">
    <section class="card">
      <div class="card-header">
        <h3 class="card-title">AI Product Comparison (Demo)</h3>
        <span class="badge badge-info">Generated {{ $report['generated_at'] }}</span>
      </div>

      <div class="table-container">
        <table class="table">
          <thead>
            <tr>
              <th>Product</th>
              <th>Our Price</th>
              <th>Competitor Price</th>
              <th>Gap</th>
              <th>Signal</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($report['rows'] as $row)
              @php
                $signalClass = match ($row['signal']) {
                  'Undercut' => 'badge-danger',
                  'Advantage' => 'badge-success',
                  default => 'badge-warning',
                };
              @endphp
              <tr>
                <td class="competition-cell-strong">{{ $row['product'] }}</td>
                <td>{{ $row['our_price'] }}</td>
                <td>{{ $row['competitor_price'] }}</td>
                <td>{{ $row['gap'] }}</td>
                <td><span class="badge {{ $signalClass }}">{{ $row['signal'] }}</span></td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </section>

    <section class="card competition-side-panel">
      <div class="card-header">
        <h3 class="card-title">Dynamic Report Sections</h3>
        <span class="badge badge-primary">From controller tags</span>
      </div>

      <div class="competition-dynamic-wrapper">
        @foreach ($report['dynamic_sections'] as $element)
          @include('admin.competition.partials.dynamic-element', ['element' => $element])
        @endforeach
      </div>
    </section>
  </div>
@endsection

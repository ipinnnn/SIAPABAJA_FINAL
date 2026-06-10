<header class="nav nav-home">
  <div class="container nav-inner">

    <a href="{{ route('home') }}" class="brand">
      <img class="brand-logo" src="{{ asset('image/Logo_Unsoed.png') }}" alt="Logo Unsoed">
      <span class="brand-name">SIAPABAJA</span>
    </a>

    {{-- Hamburger --}}
    <button class="mob-ham" id="homeNavToggle" aria-label="Buka menu">
      <i class="bi bi-list"></i>
    </button>

    {{-- Nav links + user masuk dalam hamburger --}}
    <nav class="nav-links" id="homeNavLinks">
      <a href="{{ route('home.dashboard') }}"
         class="nav-link {{ request()->routeIs(['home.dashboard','ppk.dashboard','unit.dashboard']) ? 'active' : '' }}">
        Dasbor
      </a>
      <a href="{{ route('home') }}#regulasi" class="nav-link">Regulasi</a>
      <a href="{{ route('home.pbj') }}"
         class="nav-link {{ request()->routeIs('home.pbj') ? 'active' : '' }}">
        Arsip PBJ
      </a>
      <a href="{{ route('home') }}#kontak" class="nav-link">Kontak</a>

      

    @if (!Auth::check())
  <a href="{{ route('login') }}" class="nav-link btn">Masuk</a>
@else
  {{-- Desktop only: icon kuning + dropdown --}}
  <div class="nav-user d-desktop" id="homeUserMenu">
    <button type="button" class="nav-user-btn" id="homeUserBtn" aria-label="User menu">
      <i class="bi bi-person-circle"></i>
    </button>
    <div class="nav-user-menu" id="homeUserDropdown">
      <form action="{{ url('/logout') }}" method="POST">
        @csrf
        <button type="submit" class="nav-logout">
          <i class="bi bi-box-arrow-right"></i> Keluar
        </button>
      </form>
    </div>
  </div>
@endif
    </nav>

  </div>
</header>
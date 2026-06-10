<header class="nav">
        <div class="container nav-inner">

            <a href="{{ route('landing') }}" class="brand">
                  <img class="brand-logo" src="{{ asset('image/Logo_Unsoed.png') }}" alt="Logo Universitas Jenderal Soedirman">
                        <span class="brand-name">SIAPABAJA</span>
                            </a>

                                {{-- Hamburger button (muncul di mobile) --}}
                                    <button class="mob-ham" id="mobNavToggle" aria-label="Buka menu">
                                      <i class="bi bi-list"></i>
                                      </button>

                                          <nav class="nav-links" id="navLinks">  {{-- ✅ tambah id="navLinks" --}}
                                                <a href="{{ request()->routeIs('landing') ? '#regulasi' : route('landing').'#regulasi' }}" class="nav-link">Regulasi</a>
                                                      <a href="{{ route('landing.pbj') }}" class="nav-link {{ request()->routeIs('landing.pbj') ? 'active' : '' }}">Arsip PBJ</a>
                                                            <a href="{{ request()->routeIs('landing') ? '#kontak' : route('landing').'#kontak' }}" class="nav-link">Kontak</a>
                                                                  <a class="btn btn-white" href="{{ route('login') }}">Masuk</a>
                                                                      </nav>

                                                                        </div>
                                                                        </header>
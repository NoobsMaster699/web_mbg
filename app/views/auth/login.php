<main class="login-page">
    <section class="login-visual">
        <div>
            <img src="<?= asset('images/ui/logo-spk-mbg.svg') ?>" style="width:220px" alt="SPK MBG">
            <h1>Keputusan Cerdas,<br><span class="text-green">Gizi Anak Indonesia.</span></h1>
            <p>SPK MBG membantu sekolah menentukan menu terbaik secara objektif, transparan, dan akurat.</p>
        </div>
        <div class="food-illus"></div>
        <div class="trust-row"><span>Objektif & Terukur</span><span>Transparan</span><span>Akses Aman</span></div>
    </section>
    <section class="login-form-wrap">
        <div class="card login-card">
            <div style="text-align:center;font-size:48px">SPK</div>
            <h2>Selamat Datang Kembali!</h2>
            <p class="muted" style="text-align:center">Silakan masuk untuk mengakses sistem SPK MBG khusus administrator.</p>
            <form class="form-grid" method="post" action="<?= url('login') ?>" data-loading-text="Masuk...">
                <?= csrf_field() ?>
                <label>Username
                    <input class="<?= e(field_error_class('username')) ?>" name="username" value="<?= e(old('username')) ?>" placeholder="Masukkan username Anda" required<?= field_error_attrs('username') ?>>
                    <?= field_error_html('username') ?>
                </label>
                <label>Password
                    <input class="<?= e(field_error_class('password')) ?>" name="password" type="password" placeholder="Masukkan password Anda" required<?= field_error_attrs('password') ?>>
                    <?= field_error_html('password') ?>
                </label>
                <div style="display:flex;justify-content:space-between"><label><input type="checkbox" name="remember" value="1" style="width:auto"> Ingat saya</label><a class="text-green" href="<?= url() ?>">Beranda</a></div>
                <button class="btn btn-primary" type="submit">Masuk ke Sistem</button>
                <a class="btn btn-outline" href="<?= url() ?>">Kembali ke Beranda</a>
                <div class="notice info" style="font-size:13px">Akses sistem ini dilindungi. Jangan bagikan akun Anda kepada siapapun.</div>
            </form>
        </div>
    </section>
</main>

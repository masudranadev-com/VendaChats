@extends('admin.master')

@section('title', $title)

@section('admin.content')
  @php
    $avatarInitials = strtoupper(substr(preg_replace('/\s+/', '', $profile['full_name']), 0, 2) ?: 'AD');
    $adminLanguageLabel = $localeOptions['admin_languages'][$profile['admin_language']] ?? strtoupper($profile['admin_language']);
    $websiteLanguageLabel = $localeOptions['website_languages'][$profile['website_language']] ?? strtoupper($profile['website_language']);
    $timezoneLabel = $localeOptions['timezones'][$profile['timezone']] ?? $profile['timezone'];
  @endphp

  <div class="page-header">
    <div>
      <h1 class="page-title">{{ $title }}</h1>
      <p class="page-subtitle">{{ $subtitle }}</p>
    </div>
  </div>

  <div class="settings-layout mt-md">
    <section class="settings-main-column">
      <article class="card settings-panel">
        <div class="card-header">
          <div>
            <h3 class="card-title">Profile Settings</h3>
            <p class="settings-panel-subtitle">Change your contact details, email, password, and profile image used inside the admin panel.</p>
          </div>
          <span class="badge badge-primary">{{ ucfirst($profile['product_type']) }} store</span>
        </div>

        <form
          data-profile-form
          data-api-base-url="{{ $profileApiBaseUrl }}"
          data-refresh-token="{{ $profileRefreshToken }}"
        >
          <div class="grid grid-2">
            <div class="form-group">
              <label class="form-label" for="profileFullName">Full Name</label>
              <input id="profileFullName" name="full_name" class="form-input" type="text" value="{{ $profile['full_name'] }}" required>
            </div>

            <div class="form-group">
              <label class="form-label" for="profileEmail">Email</label>
              <input id="profileEmail" name="email" class="form-input" type="email" value="{{ $profile['email'] }}" required>
            </div>

            <div class="form-group">
              <label class="form-label" for="profilePhoneNumber">Phone Number</label>
              <input id="profilePhoneNumber" name="phone_number" class="form-input" type="text" value="{{ $profile['phone_number'] }}" placeholder="+8801700000000">
            </div>

            <div class="form-group">
              <label class="form-label" for="profileAddress">Address</label>
              <input id="profileAddress" name="address" class="form-input" type="text" value="{{ $profile['address'] }}" placeholder="House, road, city">
            </div>

            <div class="form-group">
              <label class="form-label" for="profileTimezone">Timezone</label>
              <select id="profileTimezone" name="timezone" class="form-select">
                @foreach ($localeOptions['timezones'] as $value => $label)
                  <option value="{{ $value }}" {{ $value === $profile['timezone'] ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
              </select>
            </div>

            <div class="form-group">
              <label class="form-label" for="profileAdminLanguage">Admin Language</label>
              <select id="profileAdminLanguage" name="admin_language" class="form-select">
                @foreach ($localeOptions['admin_languages'] as $value => $label)
                  <option value="{{ $value }}" {{ $value === $profile['admin_language'] ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
              </select>
            </div>

            <div class="form-group">
              <label class="form-label" for="profileWebsiteLanguage">Website Language</label>
              <select id="profileWebsiteLanguage" name="website_language" class="form-select">
                @foreach ($localeOptions['website_languages'] as $value => $label)
                  <option value="{{ $value }}" {{ $value === $profile['website_language'] ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
              </select>
            </div>

            <div class="form-group">
              <label class="form-label" for="profileImageInput">Profile Image</label>
              <input id="profileImageInput" class="form-input" type="file" accept="image/*" data-profile-image-input>
              <small class="form-help">Upload a square photo or logo. The existing image stays until you save a new one.</small>
            </div>

            <div class="form-group">
              <label class="form-label" for="profileCurrentPassword">Current Password</label>
              <input id="profileCurrentPassword" name="current_password" class="form-input" type="password" autocomplete="current-password" placeholder="Required only when changing password">
            </div>

            <div class="form-group">
              <label class="form-label" for="profileNewPassword">New Password</label>
              <input id="profileNewPassword" name="new_password" class="form-input" type="password" autocomplete="new-password" placeholder="Leave blank to keep current password">
            </div>
          </div>

          <div class="page-actions">
            <button type="submit" class="btn btn-primary" data-profile-save-button>Save Profile</button>
          </div>
        </form>
      </article>
    </section>

    <aside class="settings-side-column">
      <article class="card settings-panel">
        <div class="card-header">
          <div>
            <h3 class="card-title">Account Snapshot</h3>
            <p class="settings-panel-subtitle">Live account values pulled from the current admin user profile.</p>
          </div>
          <span class="badge badge-info">{{ $profile['username'] }}</span>
        </div>

        <div class="card-body">
          <div class="flex" style="align-items:center; gap:14px; margin-bottom:16px;">
            @if ($profile['profile_image'] !== '')
              <img
                src="{{ $profile['profile_image'] }}"
                alt="{{ $profile['full_name'] }}"
                style="width:56px; height:56px; border-radius:50%; object-fit:cover; border:1px solid var(--border);"
                data-profile-avatar-image
              >
            @else
              <div class="user-avatar" style="width:56px; height:56px;" data-profile-avatar-fallback>{{ $avatarInitials }}</div>
            @endif
            <div>
              <strong style="display:block; color:var(--text-primary);" data-profile-snapshot-name>{{ $profile['full_name'] }}</strong>
              <small style="color:var(--text-tertiary);" data-profile-snapshot-email>{{ $profile['email'] }}</small>
            </div>
          </div>

          <div style="display:flex; flex-direction:column; gap:10px;">
            <div class="flex-between"><span>Phone</span><strong data-profile-snapshot-phone>{{ $profile['phone_number'] ?: 'Not set' }}</strong></div>
            <div class="flex-between"><span>Address</span><strong data-profile-snapshot-address>{{ $profile['address'] ?: 'Not set' }}</strong></div>
            <div class="flex-between"><span>Timezone</span><strong data-profile-snapshot-timezone>{{ $timezoneLabel }}</strong></div>
            <div class="flex-between"><span>Admin Language</span><strong data-profile-snapshot-admin-language>{{ $adminLanguageLabel }}</strong></div>
            <div class="flex-between"><span>Website Language</span><strong data-profile-snapshot-website-language>{{ $websiteLanguageLabel }}</strong></div>
            <div class="flex-between"><span>Joined</span><strong>{{ $profile['joined_at'] }}</strong></div>
            <div class="flex-between"><span>Last Login</span><strong>{{ $profile['last_login_at'] }}</strong></div>
            <div class="flex-between"><span>Password Updated</span><strong>{{ $profile['password_changed_at'] }}</strong></div>
          </div>
        </div>
      </article>
    </aside>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const form = document.querySelector('[data-profile-form]');
      if (!(form instanceof HTMLFormElement)) {
        return;
      }

      const apiBaseUrl = String(form.dataset.apiBaseUrl || '').trim();
      const refreshToken = String(form.dataset.refreshToken || window.localStorage.getItem('refresh_token') || '').trim();
      const saveButton = form.querySelector('[data-profile-save-button]');
      const imageInput = form.querySelector('[data-profile-image-input]');
      const snapshotName = document.querySelector('[data-profile-snapshot-name]');
      const snapshotEmail = document.querySelector('[data-profile-snapshot-email]');
      const snapshotPhone = document.querySelector('[data-profile-snapshot-phone]');
      const snapshotAddress = document.querySelector('[data-profile-snapshot-address]');
      const snapshotTimezone = document.querySelector('[data-profile-snapshot-timezone]');
      const snapshotAdminLanguage = document.querySelector('[data-profile-snapshot-admin-language]');
      const snapshotWebsiteLanguage = document.querySelector('[data-profile-snapshot-website-language]');
      let profileImageData = @json($profile['profile_image']);

      const text = (value) => String(value ?? '').trim();
      const readFileAsDataUrl = (file) => new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.onload = () => resolve(String(reader.result || ''));
        reader.onerror = () => reject(new Error('Failed to read the selected image.'));
        reader.readAsDataURL(file);
      });

      imageInput?.addEventListener('change', async () => {
        const file = imageInput.files?.[0];
        if (!file) {
          return;
        }

        try {
          profileImageData = await readFileAsDataUrl(file);
        } catch (error) {
          if (typeof window.showError === 'function') {
            window.showError(error?.message || 'Unable to load the selected image.');
          }
        }
      });

      form.addEventListener('submit', async (event) => {
        event.preventDefault();

        if (!apiBaseUrl || !refreshToken || !window.API?.Admin?.Profile?.update) {
          if (typeof window.showError === 'function') {
            window.showError('Profile API is not configured.');
          }
          return;
        }

        if (saveButton instanceof HTMLButtonElement) {
          saveButton.disabled = true;
          saveButton.textContent = 'Saving...';
        }

        const timezoneSelect = form.querySelector('[name="timezone"]');
        const adminLanguageSelect = form.querySelector('[name="admin_language"]');
        const websiteLanguageSelect = form.querySelector('[name="website_language"]');

        try {
          const payload = {
            full_name: text(form.querySelector('[name="full_name"]')?.value),
            email: text(form.querySelector('[name="email"]')?.value),
            phone_number: text(form.querySelector('[name="phone_number"]')?.value),
            address: text(form.querySelector('[name="address"]')?.value),
            profile_image: text(profileImageData),
            current_password: text(form.querySelector('[name="current_password"]')?.value),
            new_password: text(form.querySelector('[name="new_password"]')?.value),
            timezone: text(timezoneSelect?.value),
            admin_language: text(adminLanguageSelect?.value),
            website_language: text(websiteLanguageSelect?.value),
          };

          const response = await window.API.Admin.Profile.update({
            apiBaseUrl,
            refreshToken,
            payload,
            timeoutMs: 12000,
          });

          const data = response?.data || {};
          if (snapshotName instanceof HTMLElement) {
            snapshotName.textContent = text(data.full_name || payload.full_name);
          }
          if (snapshotEmail instanceof HTMLElement) {
            snapshotEmail.textContent = text(data.email || payload.email);
          }
          if (snapshotPhone instanceof HTMLElement) {
            snapshotPhone.textContent = text(data.phone_number || payload.phone_number) || 'Not set';
          }
          if (snapshotAddress instanceof HTMLElement) {
            snapshotAddress.textContent = text(data.address || payload.address) || 'Not set';
          }
          if (snapshotTimezone instanceof HTMLElement && timezoneSelect instanceof HTMLSelectElement) {
            snapshotTimezone.textContent = text(timezoneSelect.options[timezoneSelect.selectedIndex]?.textContent || timezoneSelect.value);
          }
          if (snapshotAdminLanguage instanceof HTMLElement && adminLanguageSelect instanceof HTMLSelectElement) {
            snapshotAdminLanguage.textContent = text(adminLanguageSelect.options[adminLanguageSelect.selectedIndex]?.textContent || adminLanguageSelect.value);
          }
          if (snapshotWebsiteLanguage instanceof HTMLElement && websiteLanguageSelect instanceof HTMLSelectElement) {
            snapshotWebsiteLanguage.textContent = text(websiteLanguageSelect.options[websiteLanguageSelect.selectedIndex]?.textContent || websiteLanguageSelect.value);
          }

          form.querySelector('[name="current_password"]').value = '';
          form.querySelector('[name="new_password"]').value = '';

          if (typeof window.showSuccess === 'function') {
            window.showSuccess(text(response?.message) || 'Profile updated.');
          }
        } catch (error) {
          if (typeof window.showError === 'function') {
            window.showError(error?.message || 'Failed to update profile.');
          }
        } finally {
          if (saveButton instanceof HTMLButtonElement) {
            saveButton.disabled = false;
            saveButton.textContent = 'Save Profile';
          }
        }
      });
    });
  </script>
@endsection

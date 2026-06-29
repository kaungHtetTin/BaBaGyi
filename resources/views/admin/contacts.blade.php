@extends('admin.master')

@section('content')
    <div class="container-fluid">
        @if (session('msg'))
            <div class="alert alert-success">
                {{ session('msg') }}
            </div>
        @endif

        <div class="admin-page-heading">
            <div>
                <p class="eyebrow">CONTENT SETTINGS</p>
                <h1>Contacts, Ads, and Notices</h1>
            </div>
        </div>

        <section class="panel glass misc-settings-panel">
            <div class="panel-heading">
                <div>
                    <p class="eyebrow">MISC SETTINGS</p>
                    <h2>Manage app content</h2>
                    <p class="panel-subtitle">Switch sections to edit one content type at a time.</p>
                </div>
            </div>

            <div class="misc-tab-shell">
                <nav class="misc-toolbar-tabs" role="tablist" aria-label="Misc settings sections">
                    <button class="is-active" id="contacts-tab" type="button" data-misc-tab="contacts-panel"
                        role="tab" aria-controls="contacts-panel" aria-selected="true">
                        <i class="fas fa-address-book"></i>
                        <span>Contacts</span>
                    </button>
                    <button id="ads-tab" type="button" data-misc-tab="ads-panel"
                        role="tab" aria-controls="ads-panel" aria-selected="false">
                        <i class="fas fa-image"></i>
                        <span>Ad Photos</span>
                    </button>
                    <button id="notices-tab" type="button" data-misc-tab="notices-panel"
                        role="tab" aria-controls="notices-panel" aria-selected="false">
                        <i class="fas fa-bullhorn"></i>
                        <span>Notices</span>
                    </button>
                </nav>

                <div class="misc-tab-content">
                    <section class="misc-tab-panel is-active" id="contacts-panel" role="tabpanel" aria-labelledby="contacts-tab">
                        <div class="misc-section-grid">
                            <article>
                                <div class="misc-section-heading">
                                    <div>
                                        <p class="eyebrow">CONTACT CHANNELS</p>
                                        <h3>Support contacts</h3>
                                        <p>{{ number_format($contacts->count()) }} public contact entries</p>
                                    </div>
                                </div>

                                <div class="table-wrap">
                                    <table class="table table-bordered" width="100%" cellspacing="0">
                                        <thead>
                                            <tr>
                                                <th>Service</th>
                                                <th>Contact</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($contacts as $contact)
                                                <tr>
                                                    <td><strong class="table-primary-line">{{ $contact->service }}</strong></td>
                                                    <td>{{ $contact->contact }}</td>
                                                    <td>
                                                        <a class="icon-btn small danger" href="#" data-toggle="modal" data-target="#delete-contact-{{ $contact->id }}"
                                                            aria-label="Delete {{ $contact->service }}" title="Delete">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="3"><span class="muted">No contacts found.</span></td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </article>

                            <aside class="misc-command">
                                <div class="misc-section-heading">
                                    <div>
                                        <p class="eyebrow">CREATE</p>
                                        <h3>Add contact</h3>
                                        <p>Add a public support channel.</p>
                                    </div>
                                </div>

                                <form class="settings-form" action="{{ route('admin.contacts.store') }}" method="POST">
                                    @csrf
                                    <label class="form-field">
                                        <span>Service</span>
                                        <input type="text" placeholder="Viber, Telegram, etc." name="service" value="{{ old('service') }}">
                                        @error('service')
                                            <p class="field-error">{{ $message }}</p>
                                        @enderror
                                    </label>
                                    <label class="form-field">
                                        <span>Contact</span>
                                        <input type="text" placeholder="Phone number or link" name="contact" value="{{ old('contact') }}">
                                        @error('contact')
                                            <p class="field-error">{{ $message }}</p>
                                        @enderror
                                    </label>
                                    <div class="settings-form-actions">
                                        <button class="btn primary" type="submit">
                                            <i class="fas fa-plus"></i>
                                            Add contact
                                        </button>
                                    </div>
                                </form>
                            </aside>
                        </div>
                    </section>

                    <section class="misc-tab-panel" id="ads-panel" role="tabpanel" aria-labelledby="ads-tab" hidden>
                        <div class="misc-section-grid">
                            <article>
                                <div class="misc-section-heading">
                                    <div>
                                        <p class="eyebrow">PROMOTION MEDIA</p>
                                        <h3>Ad photos</h3>
                                        <p>{{ number_format($ads->count()) }} uploaded images</p>
                                    </div>
                                </div>

                                <div class="table-wrap">
                                    <table class="table table-bordered" width="100%" cellspacing="0">
                                        <thead>
                                            <tr>
                                                <th>Preview</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($ads as $ad)
                                                <tr>
                                                    <td>
                                                        <img class="asset-thumb" src="{{ Storage::url('app/public/' . $ad->url) }}" alt="Ad photo">
                                                    </td>
                                                    <td>
                                                        <a class="icon-btn small danger" href="#" data-toggle="modal" data-target="#delete-ad-{{ $ad->id }}"
                                                            aria-label="Delete ad photo" title="Delete">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="2"><span class="muted">No ad photos found.</span></td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </article>

                            <aside class="misc-command">
                                <div class="misc-section-heading">
                                    <div>
                                        <p class="eyebrow">UPLOAD</p>
                                        <h3>Add ad photo</h3>
                                        <p>Select an image to use in app promotion areas.</p>
                                    </div>
                                </div>

                                <img id="img_preview" class="asset-preview" src="{{ asset('img/thumbnail-demo.jpg') }}" alt="Ad preview">
                                <form class="settings-form" action="{{ route('admin.ad-photo.save') }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <div class="asset-upload-control">
                                        <input id="input_image" class="sr-only-file" type="file" name="image_file" accept="image/*">
                                        <button class="btn secondary" id="choose_image" type="button">
                                            <i class="fas fa-image"></i>
                                            Choose image
                                        </button>
                                        <span class="asset-file-name" id="image_file_name">No image selected</span>
                                    </div>
                                    @error('image_file')
                                        <p class="field-error">{{ $message }}</p>
                                    @enderror
                                    <div class="settings-form-actions">
                                        <button class="btn primary" type="submit">
                                            <i class="fas fa-upload"></i>
                                            Upload photo
                                        </button>
                                    </div>
                                </form>
                            </aside>
                        </div>
                    </section>

                    <section class="misc-tab-panel" id="notices-panel" role="tabpanel" aria-labelledby="notices-tab" hidden>
                        <div class="misc-section-grid">
                            <article>
                                <div class="misc-section-heading">
                                    <div>
                                        <p class="eyebrow">ANNOUNCEMENTS</p>
                                        <h3>Notices</h3>
                                        <p>{{ number_format($notices->count()) }} active notices</p>
                                    </div>
                                </div>

                                <div class="table-wrap">
                                    <table class="table table-bordered" width="100%" cellspacing="0">
                                        <thead>
                                            <tr>
                                                <th>Content</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($notices as $notice)
                                                <tr>
                                                    <td>{{ $notice->content }}</td>
                                                    <td>
                                                        <a class="icon-btn small danger" href="#" data-toggle="modal" data-target="#delete-notice-{{ $notice->id }}"
                                                            aria-label="Delete notice" title="Delete">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="2"><span class="muted">No notices found.</span></td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </article>

                            <aside class="misc-command">
                                <div class="misc-section-heading">
                                    <div>
                                        <p class="eyebrow">CREATE</p>
                                        <h3>Add notice</h3>
                                        <p>Publish a short message for users.</p>
                                    </div>
                                </div>

                                <form class="settings-form" action="{{ route('admin.notices.save') }}" method="POST">
                                    @csrf
                                    <label class="form-field">
                                        <span>Content</span>
                                        <textarea placeholder="Promotion plan or short announcement" name="content">{{ old('content') }}</textarea>
                                        @error('content')
                                            <p class="field-error">{{ $message }}</p>
                                        @enderror
                                    </label>
                                    <div class="settings-form-actions">
                                        <button class="btn primary" type="submit">
                                            <i class="fas fa-plus"></i>
                                            Add notice
                                        </button>
                                    </div>
                                </form>
                            </aside>
                        </div>
                    </section>
                </div>
            </div>
        </section>
    </div>

    @foreach ($contacts as $contact)
        <div class="modal fade" id="delete-contact-{{ $contact->id }}" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Delete Contact</h5>
                        <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-0">Delete <strong>{{ $contact->service }}</strong> contact?</p>
                    </div>
                    <div class="modal-footer">
                        <button class="btn secondary" type="button" data-dismiss="modal">Cancel</button>
                        <form action="{{ route('admin.contacts.remove', $contact->id) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button class="btn danger">Delete</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    @foreach ($ads as $ad)
        <div class="modal fade" id="delete-ad-{{ $ad->id }}" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Delete Ad Photo</h5>
                        <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-0">Delete this ad photo?</p>
                    </div>
                    <div class="modal-footer">
                        <button class="btn secondary" type="button" data-dismiss="modal">Cancel</button>
                        <form action="{{ route('admin.ad-photo.destroy', $ad->id) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button class="btn danger">Delete</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    @foreach ($notices as $notice)
        <div class="modal fade" id="delete-notice-{{ $notice->id }}" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Delete Notice</h5>
                        <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-0">Delete this notice?</p>
                    </div>
                    <div class="modal-footer">
                        <button class="btn secondary" type="button" data-dismiss="modal">Cancel</button>
                        <form action="{{ route('admin.notices.remove', $notice->id) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button class="btn danger">Delete</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    <script>
        $(document).ready(function () {
            var tabs = $('[data-misc-tab]');
            var panels = $('.misc-tab-panel');

            tabs.on('click', function (event) {
                var targetId = $(this).data('misc-tab');

                tabs.each(function () {
                    var isActive = this === event.currentTarget;
                    $(this).toggleClass('is-active', isActive);
                    $(this).attr('aria-selected', isActive ? 'true' : 'false');
                });

                panels.each(function () {
                    var isTarget = this.id === targetId;
                    $(this).toggleClass('is-active', isTarget);
                    this.hidden = !isTarget;
                });
            });

            $('#img_preview').click(function () {
                $('#input_image').click();
            });

            $('#choose_image').click(function () {
                $('#input_image').click();
            });

            $('#input_image').change(function () {
                var files = $('#input_image').prop('files');
                var file = files[0];

                if (!file) return;

                $('#image_file_name').text(file.name);

                var reader = new FileReader();
                reader.onload = function (event) {
                    $('#img_preview').attr('src', event.target.result);
                };

                reader.readAsDataURL(file);
            });
        });
    </script>
@endsection

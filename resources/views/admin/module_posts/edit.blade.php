@extends('layouts.admin_app')

@section('title', 'Edit '.$entitySingular)

@section('content')
    <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
        <div>
            <h1 class="page-title fw-semibold fs-18 mb-0">Edit {{ $entitySingular }}</h1>
            <div class="text-muted fs-13">{{ $record->title }}</div>
        </div>
        <a href="{{ route($routePrefix.'.index') }}" class="btn btn-secondary">Back to list</a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="card custom-card">
        <div class="card-body">
            <form action="{{ route($routePrefix.'.update', [$routeParam => $record->getKey()]) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    @if($routePrefix === 'admin.movies')
                        <div class="col-12">
                            <div class="p-3 border rounded-2 border-primary border-opacity-25">
                                <label class="form-label">IMDb / Movie API search</label>
                                <div class="input-group">
                                    <input type="text" id="imdb_query" class="form-control" placeholder="Type movie title or IMDb ID (e.g. tt0133093)">
                                    <button type="button" id="imdb_fetch_btn" class="btn btn-primary">Fetch</button>
                                </div>
                                <div class="form-text">Fetch and overwrite title/description/IMDb fields from API.</div>
                                <div id="imdb_feedback" class="small mt-2"></div>
                            </div>
                        </div>
                    @endif
                    <div class="col-md-8">
                        <label class="form-label">Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" value="{{ old('title', $record->title) }}" class="form-control" required maxlength="255">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select" required>
                            <option value="draft" @selected(old('status', $record->status) === 'draft')>Draft</option>
                            <option value="published" @selected(old('status', $record->status) === 'published')>Published</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Slug (optional)</label>
                        <input type="text" name="slug" value="{{ old('slug', $record->slug) }}" class="form-control" maxlength="255">
                    </div>
                    @if($routePrefix === 'admin.movies')
                        <div class="col-md-3">
                            <label class="form-label">IMDb ID</label>
                            <input type="text" name="imdb_id" value="{{ old('imdb_id', $record->imdb_id) }}" class="form-control" maxlength="20" placeholder="tt1234567">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Release year</label>
                            <input type="text" name="release_year" value="{{ old('release_year', $record->release_year) }}" class="form-control" maxlength="20" placeholder="2024">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">IMDb rating</label>
                            <input type="text" name="imdb_rating" value="{{ old('imdb_rating', $record->imdb_rating) }}" class="form-control" maxlength="20" placeholder="8.3">
                        </div>
                        <div class="col-md-9">
                            <label class="form-label">Poster URL</label>
                            <input type="url" name="poster_url" value="{{ old('poster_url', $record->poster_url) }}" class="form-control" maxlength="2000" placeholder="https://...">
                        </div>
                    @endif
                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="4">{{ old('description', $record->description) }}</textarea>
                    </div>
                    @if($routePrefix === 'admin.movies')
                        <div class="col-12">
                            @php($posterPreview = old('poster_url', $record->poster_url))
                            <div id="poster_preview_wrap" class="{{ $posterPreview ? '' : 'd-none' }}">
                                <label class="form-label">Poster preview</label>
                                <div>
                                    <img id="poster_preview" src="{{ $posterPreview }}" alt="Poster preview" class="img-thumbnail" style="max-height:220px;">
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="mt-4">
                    <h6 class="mb-2">Download links</h6>
                    <p class="text-muted fs-13 mb-2">Saving replaces all links below. Empty URL rows are ignored.</p>
                    @include('admin.module_posts._download_rows', ['rows' => $downloadRows])
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
    @if($routePrefix === 'admin.movies')
        <script>
            (function () {
                const fetchBtn = document.getElementById('imdb_fetch_btn');
                const queryInput = document.getElementById('imdb_query');
                const feedback = document.getElementById('imdb_feedback');
                const fields = {
                    title: document.querySelector('input[name="title"]'),
                    description: document.querySelector('textarea[name="description"]'),
                    imdb_id: document.querySelector('input[name="imdb_id"]'),
                    poster_url: document.querySelector('input[name="poster_url"]'),
                    release_year: document.querySelector('input[name="release_year"]'),
                    imdb_rating: document.querySelector('input[name="imdb_rating"]'),
                };
                const posterWrap = document.getElementById('poster_preview_wrap');
                const posterImg = document.getElementById('poster_preview');

                function setFeedback(message, isError) {
                    feedback.className = 'small mt-2 ' + (isError ? 'text-danger' : 'text-success');
                    feedback.textContent = message;
                }

                function refreshPosterPreview() {
                    const value = (fields.poster_url?.value || '').trim();
                    if (!posterWrap || !posterImg) return;
                    if (value === '') {
                        posterWrap.classList.add('d-none');
                        posterImg.setAttribute('src', '');
                        return;
                    }
                    posterImg.setAttribute('src', value);
                    posterWrap.classList.remove('d-none');
                }

                fields.poster_url?.addEventListener('input', refreshPosterPreview);

                fetchBtn?.addEventListener('click', async function () {
                    const query = (queryInput?.value || '').trim();
                    if (query.length < 2) {
                        setFeedback('Type at least 2 characters.', true);
                        return;
                    }

                    fetchBtn.disabled = true;
                    setFeedback('Fetching movie info...', false);
                    try {
                        const url = new URL('{{ route('admin.movies.imdb.lookup') }}', window.location.origin);
                        url.searchParams.set('query', query);
                        const response = await fetch(url.toString(), { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                        const json = await response.json();
                        if (!response.ok) {
                            setFeedback(json.message || 'Could not fetch movie details.', true);
                            return;
                        }

                        Object.keys(fields).forEach(function (key) {
                            if (fields[key] && json[key] != null) {
                                fields[key].value = json[key];
                            }
                        });
                        refreshPosterPreview();
                        setFeedback('Movie details loaded. Review and update.', false);
                    } catch (error) {
                        setFeedback('Request failed. Please try again.', true);
                    } finally {
                        fetchBtn.disabled = false;
                    }
                });
            })();
        </script>
    @endif
@endsection

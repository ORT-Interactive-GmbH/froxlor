@servers(['web' => [$user.'@'.$host]])

@setup
	if ( ! isset($user) ) {
		throw new Exception('SSH login username/host is not set');
	}

	if ( ! isset($repo) ) {
		throw new Exception('Git repository is not set');
	}
@endsetup

@task('install', ['confirm' => false])
	cd /srv/customers/webs/web1/{{ $folder }};
	git clone git@bitbucket.org:ortinteractivegmbh/{{ $repo }};
	cd {{ $repo }};
	cp .env.example .env
    composer install --no-interaction;
@endtask
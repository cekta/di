 docker run \
  --rm \
  -it \
  -v "$PWD/docs":/srv/jekyll \
  -v "$PWD/bundle":/usr/local/bundle \
  -p 4000:4000 \
  jekyll/jekyll jekyll serve

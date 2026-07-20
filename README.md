# Odoru GitHub Profile Views Counter

A small PHP project that regenerates a static SVG badge every 12 hours with GitHub Actions.

## What it does

- Reads GitHub traffic views from the GitHub REST API
- Renders a badge SVG
- Writes the result to `public/views.svg`
- Commits the SVG back to the repo on a 12-hour schedule

## Install

```bash
composer install
cp .env.example .env
```

## Configure

Set `GITHUB_REPOSITORY` to the repository whose traffic you want to read.

Optional:

- `TRAFFIC_TOKEN` or `GITHUB_TOKEN`
- `INPUT_COUNT_TYPE=count|uniques`
- `INPUT_BADGE_LABEL`
- `INPUT_BADGE_COLOR`
- `INPUT_BADGE_STYLE`
- `INPUT_BASE_COUNT`
- `INPUT_ABBREVIATED=true|false`

## Build locally

```bash
php bin/build-svg.php
```

The SVG will be written to `public/views.svg` by default.

## GitHub Actions

The workflow in `.github/workflows/build-svg.yml` runs every 12 hours and pushes the generated SVG if it changed.

## Notes

This fork is deliberately static. No Docker, no web server, no unnecessary layers pretending to be architecture.

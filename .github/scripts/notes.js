module.exports = async ({ github, context, core, fetch }) => {
  const { TAGS_MATRIX, META_PACKAGE } = process.env
  const tags = JSON.parse(TAGS_MATRIX)

  return Promise.allSettled(
    tags.map((tag_name) =>
      core.group(tag_name, async (tag_name) => {
        const slug = `version-${tag_name.replaceAll('.', '-')}`
        let body = ''

        try {
          const res = await fetch(
            'https://wordpress.org/documentation/wp-json/wp/v2/wordpress-versions?per_page=50'
          )
          const data = await res.json()

          const release = data.find((tag) => tag.slug === slug)
          if (!release) {
            throw Error('Release not found')
          }

          const { link } = release
          body = release.content?.rendered?.split('<h2', 4)[2]
          if (!body) {
            throw Error('Release body is empty or unexpected')
          }

          core.info(tag_name)
          body = `_Sourced from [WordPress.org Documentation](${link})._\n\n<h2${body}`
        } catch (e) {
          core.info(tag_name)
          core.error(e)

          body = `_Version notes available on [WordPress.org Documentation](https://wordpress.org/documentation/wordpress-version/${slug}/)._`
        }

        core.info('Publishing')
        return github.rest.repos.createRelease({
          owner: context.repo.owner,
          repo: META_PACKAGE.substring(META_PACKAGE.indexOf('/') + 1),
          tag_name,
          body,
          name: `Version ${tag_name}`,
          make_latest: 'legacy',
        })
      })
    )
  )
}

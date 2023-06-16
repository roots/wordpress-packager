module.exports = async ({ fetch }) => {
  const { VERSION } = process.env
  const slug = `version-${VERSION.replaceAll('.', '-')}`

  try {
    const res = await fetch('https://wordpress.org/documentation/wp-json/wp/v2/wordpress-versions?per_page=50')
    const data = await res.json()

    const release = data.find((tag) => tag.slug === slug)
    if (!release) {
      throw Error('Release not found')
    }

    const { link } = release
    const body = release.content?.rendered?.split('<h2', 4)[2]
    if (!body) {
      throw Error('Release body is empty or unexpected')
    }

    return `_Sourced from [WordPress.org Documentation](${link})._\n\n<h2${body}`
  } catch (e) {
    console.log(e)

    return `_Version notes available on [WordPress.org Documentation](https://wordpress.org/documentation/wordpress-version/${slug}/)._`
  }
}

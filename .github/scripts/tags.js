module.exports = async ({ github, context }) => {
  const { PACKAGE, META } = process.env

  const [
    { data: currrentTags1 },
    { data: currrentTags2 },
    { data: upstreamTags1 },
    { data: upstreamTags2 },
  ] = await Promise.all([
    github.rest.repos.listTags({
      owner: context.repo.owner,
      repo: META.substring(META.indexOf('/') + 1),
      per_page: 100,
      page: 1,
    }),
    github.rest.repos.listTags({
      owner: context.repo.owner,
      repo: META.substring(META.indexOf('/') + 1),
      per_page: 100,
      page: 2,
    }),
    github.rest.repos.listTags({
      owner: context.repo.owner,
      repo: PACKAGE.substring(PACKAGE.indexOf('/') + 1),
      per_page: 100,
      page: 1,
    }),
    github.rest.repos.listTags({
      owner: context.repo.owner,
      repo: PACKAGE.substring(PACKAGE.indexOf('/') + 1),
      per_page: 100,
      page: 2,
    }),
  ])

  const currrentTags = currrentTags1.concat(currrentTags2)
  const upstreamTags = upstreamTags1.concat(upstreamTags2)

  const targetTags = await Promise.allSettled(
    upstreamTags
      .filter((tag) => !currrentTags.find((t) => t.name === tag.name))
      .map((tag) =>
        github.rest.git
          .getRef({
            owner: context.repo.owner,
            repo: META.substring(META.indexOf('/') + 1),
            ref: 'tags/' + tag.name,
          })
          .then(() => {})
          .catch((res) => ({ tag: tag.name, status: res.status }))
      )
  )

  return targetTags
    .filter(({ value }) => value?.status === 404)
    .map(({ value }) => value.tag)
}

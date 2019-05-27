# Test resources

To update the test resources

```bash
curl -s "https://wordpress.org/download/releases/" > repo-pass-1.json
```

Then to update the json files:

```bash
./update-urls-json.sh
```

You might have to update the exclusions or manually delete urls that you know aren't valid.

[jq]: https://stedolan.github.io/jq/
[pup]: https://github.com/ericchiang/pup
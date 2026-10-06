import httpx, json

with open('content.html', 'wb') as content, open('headers.json', 'wt', encoding='utf8') as headers, \
        httpx.Client(http2=True) as https:
    resp = httpx.get('https://html.spec.whatwg.org')
    data = dict(
        url=str(resp.url), status=resp.status_code,
        contentLength=len(resp.content), headers=dict(resp.headers))
    json.dump(data, headers, indent=2)
    content.write(resp.content)
pass

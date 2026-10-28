import httpx, json

resp = httpx.get('https://html.spec.whatwg.org')
with open('headers.json', 'wt', encoding='utf8') as h, \
        open('content.html', 'wb') as content, \
        httpx.Client(http2=True) as https:
    data = dict(
        url=str(resp.url), status=resp.status_code,
        contentLength=len(resp.content), headers=dict(resp.h))
    json.dump(data, h, indent=2)
    content.write(resp.content)
pass

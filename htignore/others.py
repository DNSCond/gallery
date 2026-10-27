import pathlib, hashlib, base64, re, requests, json
from custom_format import parse_named_blocks

truncated_len = 8
image_formats = {'.png', '.jpeg', '.jpg', '.webp', '.avif'}


def makeready2():
    data = dict()
    folder = 'imgdata'
    alt_texts = dict()
    with open('misc-ai/.altText.txt', 'rt', encoding='utf8') as file:
        alt_texts = parse_named_blocks(file.read())
    for path in pathlib.Path('misc-ai').iterdir():
        if path.suffix not in image_formats:
            continue
        with open(path, 'rb') as file:
            content = file.read()
            hashed = base64.b64encode(hashlib.sha512(content).digest()).decode('utf-8') \
                .replace('/', '_').replace('+', '-').replace('=', str())[0::truncated_len]
        with open(f'../{folder}/{hashed}{path.suffix}', 'wb') as file:
            file.write(content)
        resp = requests.get(f'http://localhost/gallery/dev-only/imgdata.php?hash={hashed}{path.suffix}')
        sizes = resp.headers.get('image-size')
        if sizes is None:
            continue
        matched = re.search('w=(\\d+), h=(\\d+)', sizes)
        data[f'{hashed}{path.suffix}'] = {
            'w': int(matched.group(1)), 'h': int(matched.group(2)),
            't': resp.headers['image-type'], 'oname': path.name}
        data[f'{hashed}{path.suffix}']['alt'] = alt_texts.get(path.name, path.name)
    # Sort data's entries by the order of keys in alt_texts using each entry's oname
    sorted_data = dict()
    for alt_key in alt_texts.keys():
        for k, v in data.items():
            if v.get('oname') == alt_key:
                sorted_data[k] = v
                break
    # Include any remaining entries that weren't in alt_texts
    for k, v in data.items():
        if k not in sorted_data:
            sorted_data[k] = v
    data = sorted_data
    with open(f'../{folder}/.misc-ai.json', 'wt', encoding='utf8') as file:
        file.write(json.dumps(data))
    return {'errored': False}


pass

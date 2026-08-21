import pathlib  # , hashlib

paths = [
    pathlib.Path('404placeholder.png'),
    *pathlib.Path('images').glob('*/*.*'),
    # *pathlib.Path('comic-images').glob('*/*/*.*'),
    *pathlib.Path('universe-images').glob('*/*/*.*'),
    *pathlib.Path('universe-images').glob('*/*.*'),
]
if pathlib.Path('images/universe-img.webp').exists():
    paths.append(pathlib.Path('images/universe-img.webp'))
if pathlib.Path('images/universe-img.avif').exists():
    paths.append(pathlib.Path('images/universe-img.avif'))

paths = set(path.parent / path.stem for path in paths if path.is_file() and not (
        str(path).endswith('.kra') or str(path).endswith('.json')))  # or str(path).endswith('.kra~')
with (open('../fileformat.html', 'wt') as htmlout):
    htmlout.write('<!DOCTYPE html><html lang=en><meta charset=UTF-8><meta name=viewport content=\'width=device-width,'
                  'initial-scale=1\'><style>html{font-family: monospace;}.eTrue{background-color:mediumseagreen}'
                  '.eFalse{background-color:tomato}</style><link rel=stylesheet href=ddDL-table.css><meta name=robots'
                  ' content=noindex><body><table><thead><tr><th scope=row>file path<th scope=row>Avif<th scope='
                  'row>Webp<th scope=row>PNG<th scope=row>JPG<th scope=row>JPEG<th scope=row>KRA~<tbody>')
    for path in sorted(paths, key=str):
        # with open(path, 'rb') as file:
        #     hash256 = hashlib.sha256(file.read()).hexdigest()
        htmlout.write(f'<tr><td>{str(path)}')
        for i in ['.avif', '.webp', '.png', '.jpg', '.jpeg','.kra~']:
            exists = pathlib.Path(str(path) + i).exists()
            htmlout.write(f'<td class=e{exists}>' + str(exists))
        htmlout.write(f'</tr>')
    htmlout.write('</tbody></table>')
pass

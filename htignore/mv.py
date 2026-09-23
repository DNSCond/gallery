from pathlib import Path
import shutil, json

mv = [str((Path('images') / i.split('/')[1]) if
          i.split('/')[0] == 'main' else (Path('universe-images') / i)
          ) for i in input('mv:').strip().split(' ')]
it = iter(mv)
mv = dict(zip(it, it))
# main/Pinkey AttachedEdu/Pinkey main/14-Y-C AttachedEdu/14-Y-C main/08-B-R AttachedEdu/08-B-R
for k, v in mv.items():
    p = Path(v)
    f = Path(k)
    local = p.parent.name if 'universe-images' in v else 'main'
    new_location = f'{local}/{p.name}'
    if p.exists():
        print(f'new_location already exists, skipping it')
        continue
    for file in f.iterdir():
        p.mkdir(exist_ok=True, parents=True)
        with open(file, 'rb') as src, open(p / file.name, 'wb') as out:
            out.write(src.read())
    shutil.rmtree(f)
    f.mkdir(exist_ok=True, parents=True)
    with open(f / 'main.json', 'wt') as out:
        out.write(json.dumps(dict(location=new_location)))

pass

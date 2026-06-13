# import json
# import pathlib, re
#
# for p in pathlib.Path(r"D:\var\www\krexpress\antboiy\multicolor").iterdir():
#     if bool(const := re.search('(\\d{2})-([a-zA-Z\\-]+)$', str(p))):
#         if const.group(1) == '00': continue
#         if (p / 'creationDate.txt').is_file():
#             pathlib.Path(f'images/{const.group(1)}-{const.group(2)}').mkdir(parents=True, exist_ok=True)
#             with (open(p / 'creationDate.txt', 'rt', encoding='utf8') as src,
#                   open(f'images/{const.group(1)}-{const.group(2)}/main.json', 'wt', encoding='utf8') as out):
#                 datetime = re.search('(\\d{4}-\\d{2}-\\d{2}T\\d{2}:\\d{2}:\\d{2}Z?)', src.read())
#                 if datetime:
#                     datetime = datetime.group(1)
#                     if 'Z' not in datetime:
#                         datetime += 'Z'
#                     out.write(json.dumps({
#                         'creationDate': datetime,
#                     }))
#                 else:
#                     out.write(json.dumps(dict(private=True)))
#                 pass
#             for imagepath in p.iterdir():
#                 src_path = p / imagepath
#                 if not re.search('\\.png', str(src_path)): continue
#                 if not re.search('main\\.png$', str(src_path)): continue
#                 out_path = pathlib.Path('images') / imagepath.parents[0].name/ imagepath.name
#                 with (open(src_path, 'rb') as src,
#                       open(out_path, 'wb') as out):
#                     out.write(src.read())
#                 pass
#     pass
# pass

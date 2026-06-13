# import pathlib, json, shutil
#
# for source_dir in pathlib.Path('images').iterdir():
#     if not source_dir.is_dir(): continue
#     with open(source_dir / 'main.json', 'rt', encoding='utf8') as file:
#         destination_dir = pathlib.Path('universe-images') / json.loads(file.read())['UniverseId'] / source_dir.name
#     shutil.copytree(source_dir, destination_dir, dirs_exist_ok=True)
# pass

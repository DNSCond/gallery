#from datetime import datetime, timezone
import json, pathlib

#iso_string = datetime.now(timezone.utc)\
#             .isoformat(timespec='milliseconds')\
#             .replace('+00:00', 'Z')
#iso_string = re.sub('\\.\\d+', '', iso_string)
imagep=pathlib.Path('images')
images=pathlib.Path('universe-images/Favicond-Unknown')
if (images / (path := input('charactername:'))).exists():
    input('name already exists')
    exit()

(images / path).mkdir(parents=True, exist_ok=True)
with open(images / path / 'main.json',
          'wt', encoding='utf8') as file:
    evilize = '1970-01-01T00:00:00Z'
    file.write(json.dumps(dict(
        name='/*Unknown-Name*/',
        UniverseId='Favicond-Unknown',
        creationDate=evilize,
        LastModified=evilize,
        registerDate=evilize,
    ), indent=2))
with (open(imagep / 'placeholder.kra', 'rb') as src,
      open(images / path / 'main.kra', 'wb') as out):
    out.write(src.read())
pass

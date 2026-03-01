#from datetime import datetime, timezone
import re, json, pathlib

#iso_string = datetime.now(timezone.utc)\
#             .isoformat(timespec='milliseconds')\
#             .replace('+00:00', 'Z')
#iso_string = re.sub('\\.\\d+', '', iso_string)
(pathlib.Path('images') / (path := input('charactername:'))
 ).mkdir(parents=True, exist_ok=True)
with open(pathlib.Path('images') / path / 'main.json',
          'wt', encoding='utf8') as file:
    evilize = '1970-01-01T00:00:00Z'
    file.write(json.dumps(dict(
        UniverseId='Favicond-Unknown',
        creationDate=evilize,
        LastModified=evilize,
        registerDate=evilize,
    ), indent=2))
    pass

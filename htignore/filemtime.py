from datetime import datetime, timezone
import os, re

timestamp = re.sub('^"', '', re.sub('"$', '', input('filemtime:')))
mdt = datetime.fromtimestamp(os.path.getmtime(timestamp), tz=timezone.utc)
cdt = datetime.fromtimestamp(os.path.getctime(timestamp), tz=timezone.utc)

iso_string_m = mdt.isoformat().replace("+00:00", "Z")

iso_string_c = cdt.isoformat().replace("+00:00", "Z")
print(re.sub('\\.\\d+','',f'mtime: {iso_string_m}\nctime: {iso_string_c}'))
input('enter to close:')

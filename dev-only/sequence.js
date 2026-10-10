// dev-only/sequence.js
export function getNumberedSequence(length) {
    return Object.keys(Array.from({length}));
}

export function shuffleSingleCryptoCall(arr) {
  // Fisher-Yates needs n - 1 random indices
  const n = arr.length;
  if (n <= 1) return arr;

  // 1. Single call to crypto.getRandomValues to pre-generate all needed entropy
  const randomValues = new Uint32Array(n - 1);
  crypto.getRandomValues(randomValues);

  // 2. Perform Fisher-Yates shuffle using the pre-generated array
  for (let i = n - 1; i > 0; i--) {
    // Scale the 32-bit integer to a valid index in range [0, i]
    const j = Math.floor((randomValues[i - 1] / 0x100000000) * (i + 1));
    
    // Swap elements arr[i] and arr[j]
    const temp = arr[i];
    arr[i] = arr[j];
    arr[j] = temp;
  }

  return arr;
}

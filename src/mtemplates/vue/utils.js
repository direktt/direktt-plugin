export function getFileExtension(url) {
  // Remove query parameters and fragments
  url = url.split('?')[0].split('#')[0];
  // Find the part after the last slash
  const path = url.substring(url.lastIndexOf('/') + 1);

  // If there's no dot, or dot is the first character, there's no extension
  if (!path.includes('.') || path.startsWith('.')) return '';

  // Return the substring after the last dot
  return path.split('.').pop().toLowerCase();
}

export function getFilenameFromUrl(url) {
  // Remove query parameters and fragments
  url = url.split('?')[0].split('#')[0];
  // Get the part after the last slash
  const path = url.substring(url.lastIndexOf('/') + 1);
  return path;
}

export function extractUpTo50(str) {
  if (str.length <= 50) return str;
  const trimmed = str.slice(0, 50);
  const lastSpace = trimmed.lastIndexOf(' ');
  if (lastSpace === -1) {
    // No spaces, just return up to 50 chars
    return trimmed;
  }
  return trimmed.slice(0, lastSpace);
}

export function getDateSeparatorLabel(dateObj) {
  const now = new Date();
  // Set both times to midnight for comparison
  const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
  const yesterday = new Date(today);
  yesterday.setDate(today.getDate() - 1);

  const dateOnly = new Date(
    dateObj.getFullYear(),
    dateObj.getMonth(),
    dateObj.getDate()
  );

  if (dateOnly.getTime() === today.getTime()) {
    return "Today";
  } else if (dateOnly.getTime() === yesterday.getTime()) {
    return "Yesterday";
  } else {
    // Format as dd MM yyyy e.g. 02 December 2025
    return dateObj.toLocaleDateString("en-GB", {
      day: "2-digit",
      month: "long",
      year: "numeric",
    });
  }
}

export function trimmedIfStartsWithIco(str) {
  // Left trim (remove spaces only from start)
  const leftTrimmed = str.replace(/^\s+/, '');
  if (leftTrimmed.startsWith('ico')) {
    // Fully trim and return
    return leftTrimmed.trim();
  }
  return null;
}

export function normalizeUrl(url) {
  if (!/^https?:\/\//i.test(url)) {
    return `https://${url}`;
  }
  return url;
}
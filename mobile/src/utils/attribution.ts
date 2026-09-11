/**
 * Household lists (parcels/reservations) can include items reserved by a
 * housemate, or items a household member sent to someone else entirely
 * (peer-to-peer). This picks the right "· for X" / "· sent to X" suffix
 * for the current viewer, or '' when the item is simply theirs.
 */
export function attributionSuffix(
  myName: string | null | undefined,
  reservedBy: string | null | undefined,
  sentBy: string | null | undefined,
): string {
  if (sentBy && sentBy === myName && reservedBy && reservedBy !== myName) {
    return ` · sent to ${reservedBy}`;
  }
  if (reservedBy && reservedBy !== myName) {
    return ` · for ${reservedBy}`;
  }
  return '';
}

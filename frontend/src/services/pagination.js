/**
 * Laravel returns two shapes from this API:
 *
 *   paginated  { data: [...], links: {...}, meta: { current_page, last_page, per_page, total, ... } }
 *   plain      { data: [...] }                       (endpoints that use ->get())
 *
 * Both are real, so every store normalises through here rather than each one
 * re-implementing the guesswork.
 */
export function extractList(response) {
  const body = response?.data

  if (Array.isArray(body)) {
    return { data: body, meta: null, links: null }
  }

  if (Array.isArray(body?.data)) {
    return {
      data: body.data,
      meta: body.meta ?? null,
      links: body.links ?? null,
    }
  }

  return { data: body ? [body] : [], meta: null, links: null }
}

/** A single resource, whether or not it is wrapped in `data`. */
export function extractItem(response) {
  const body = response?.data
  return body?.data ?? body ?? null
}

/**
 * Turn an Axios failure into something a view can render directly.
 * Laravel validation errors keep their per-field shape.
 */
export function normalizeError(error) {
  const status = error?.response?.status ?? null
  const payload = error?.response?.data ?? {}

  return {
    status,
    message:
      payload.message ||
      (status === 403
        ? 'You are not allowed to do that.'
        : status === 404
          ? 'That record could not be found.'
          : error?.message || 'Something went wrong. Please try again.'),
    errors: payload.errors ?? {},
    isValidation: status === 422,
    isForbidden: status === 403,
    isNotFound: status === 404,
  }
}

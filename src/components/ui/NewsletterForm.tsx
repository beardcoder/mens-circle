import Button from '@/components/ui/Button'
import { Input } from '@/components/ui/Input'
import directus from '@/lib/directus'
import { createItem, readItems } from '@directus/sdk'
import { email, FormError, required, setResponse, useForm, type SubmitHandler } from '@modular-forms/preact'
import type { FunctionComponent } from 'preact'

type NewsletterForm = {
  first_name: string
  last_name: string
  email: string
}

const NewsletterForm: FunctionComponent<{}> = ({}) => {
  const [newsletterForm, { Form, Field }] = useForm<NewsletterForm>()

  const handleSubmit: SubmitHandler<NewsletterForm> = async (values) => {
    try {
      const subscriptions = await directus.request(
        readItems('NewsletterSubscription', {
          filter: { email: { _eq: values.email } },
          fields: ['*'],
        }),
      )
      let subscription = subscriptions[0]

      if (subscription) {
        throw new FormError<NewsletterForm>('Du bist bereits für dieses Event angemeldet.')
      } else {
        await directus.request(createItem('NewsletterSubscription', { ...values }))
      }

      setResponse(newsletterForm, {
        status: 'success',
        message: 'Vielen Dank für deine Anmeldung',
      })
    } catch (error) {
      const errorMessage = error instanceof Error ? error.message : 'Ein unbekannter Fehler ist aufgetreten.'
      setResponse(newsletterForm, {
        status: 'error',
        message: errorMessage,
      })
    }
  }

  return (
    <div>
      <div>
        {newsletterForm.response.value.status === 'success' && (
          <div
            class="flex items-center p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 dark:bg-stone-800 dark:text-green-400"
            role="alert"
          >
            <svg
              class="shrink-0 inline w-4 h-4 me-3"
              aria-hidden="true"
              xmlns="http://www.w3.org/2000/svg"
              fill="currentColor"
              viewBox="0 0 20 20"
            >
              <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z" />
            </svg>
            <span class="sr-only">Info</span>
            <div>{newsletterForm.response.value.message}</div>
          </div>
        )}
        {newsletterForm.response.value.status === 'error' && (
          <div
            class="flex items-center p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-stone-800 dark:text-red-400"
            role="alert"
          >
            <svg
              class="shrink-0 inline w-4 h-4 me-3"
              aria-hidden="true"
              xmlns="http://www.w3.org/2000/svg"
              fill="currentColor"
              viewBox="0 0 20 20"
            >
              <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z" />
            </svg>
            <span class="sr-only">Info</span>
            <div>{newsletterForm.response.value.message}</div>
          </div>
        )}
        <Form className="space-y-6" onSubmit={handleSubmit}>
          <div class="flex w-full space-x-4">
            <div class="w-1/2">
              <Field name="first_name" validate={[required('Bitte gib deinen Vornamen ein.')]}>
                {(field, props) => (
                  <>
                    <label for={field.name} className="sr-only mb-2 block text-sm font-medium">
                      Vorname
                    </label>
                    <Input
                      {...props}
                      id={field.name}
                      value={field.value}
                      placeholder="Vorname"
                      className="w-full"
                      required
                    />
                    {field.error && <div class="text-red-500 text-sm mt-2">{field.error}</div>}
                  </>
                )}
              </Field>
            </div>
            <div class="w-1/2">
              <Field name="last_name" validate={[required('Bitte gib deinen Nachnamen ein.')]}>
                {(field, props) => (
                  <>
                    <label htmlFor={field.name} className="sr-only mb-2 block text-sm font-medium">
                      Nachname
                    </label>
                    <Input
                      {...props}
                      id={field.name}
                      value={field.value}
                      placeholder="Nachname"
                      className="w-full"
                      required
                    />
                    {field.error && <div class="text-red-500 text-sm mt-2">{field.error}</div>}
                  </>
                )}
              </Field>
            </div>
          </div>
          <div>
            <Field
              name="email"
              validate={[
                required('Bitte gib deine E-Mail-Adresse ein.'),
                email('Die E-Mail-Adresse ist falsch formatiert.'),
              ]}
            >
              {(field, props) => (
                <>
                  <label for={field.name} className="sr-only mb-2 block text-sm font-medium">
                    E-Mail
                  </label>
                  <Input {...props} id={field.name} type="email" placeholder="E-Mail" value={field.value} required />
                  {field.error && <div class="text-red-500 text-sm mt-2">{field.error}</div>}
                </>
              )}
            </Field>
          </div>
          <Button type="submit" className="w-full" disabled={newsletterForm.submitting.value}>
            {newsletterForm.submitting.value && (
              <svg xmlns="http://www.w3.org/2000/svg" width="1.2em" height="1.2em" viewBox="0 0 24 24" class="mr-2">
                <path fill="currentColor" d="M18.364 5.636L16.95 7.05A7 7 0 1 0 19 12h2a9 9 0 1 1-2.636-6.364" />
              </svg>
            )}
            Anmelden
          </Button>
        </Form>
      </div>
    </div>
  )
}

export default NewsletterForm

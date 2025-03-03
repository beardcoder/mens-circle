import directus from '@/lib/directus'
import { createItem } from '@directus/sdk'
import { email, FormError, required, setResponse, useForm, type SubmitHandler } from '@modular-forms/preact'
import type { FunctionComponent } from 'preact'
import Button from './ui/Button'
import { Input } from './ui/Input'
import { Textarea } from './ui/Textarea'

type ContactForm = {
  name: string
  email: string
  message: string
}

const ContactForm: FunctionComponent = () => {
  const [contactForm, { Form, Field }] = useForm<ContactForm>()

  const handleSubmit: SubmitHandler<ContactForm> = async (values) => {
    try {
      await directus.request(createItem('contact_requests', { ...values }))
    } catch (error) {
      const errorMessage = error instanceof Error ? error.message : 'Ein unbekannter Fehler ist aufgetreten.'
      throw new FormError<ContactForm>(errorMessage)
    }
    setResponse(contactForm, {
      status: 'success',
      message: 'Vielen Dank für deine Nachricht. Ich melde mich bald bei dir.',
    })
  }

  return (
    <div>
      <div className="mx-auto w-full max-w-lg">
        {contactForm.response.value.status === 'success' && (
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
            <div>{contactForm.response.value.message}</div>
          </div>
        )}
        {contactForm.response.value.status === 'error' && (
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
            <div>{contactForm.response.value.message}</div>
          </div>
        )}
        <Form className="space-y-8" onSubmit={handleSubmit}>
          <Field name="name" validate={[required('Bitte gib deinen Namen ein.')]}>
            {(field, props) => (
              <div>
                <label for={field.name} className="sr-only mb-2 block text-sm font-medium">
                  Name
                </label>
                <Input {...props} id={field.name} value={field.value} placeholder="Name" className="w-full" required />
                {field.error && <div class="text-red-500 text-sm mt-2">{field.error}</div>}
              </div>
            )}
          </Field>

          <Field
            name="email"
            validate={[
              required('Bitte gib deine E-Mail-Adresse ein.'),
              email('Die E-Mail-Adresse ist falsch formatiert.'),
            ]}
          >
            {(field, props) => (
              <div>
                <label for={field.name} className="sr-only mb-2 block text-sm font-medium">
                  E-Mail
                </label>
                <Input {...props} id={field.name} type="email" placeholder="E-Mail" value={field.value} required />
                {field.error && <div class="text-red-500 text-sm mt-2">{field.error}</div>}
              </div>
            )}
          </Field>
          <Field name="message" validate={[required('Bitte gib eine Nachricht ein.')]}>
            {(field, props) => (
              <div>
                <label for={field.name} className="sr-only mb-2 block text-sm font-medium">
                  Nachricht
                </label>
                <Textarea {...props} id={field.name} placeholder="Nachricht" value={field.value} required />
                {field.error && <div class="text-red-500 text-sm mt-2">{field.error}</div>}
              </div>
            )}
          </Field>
          <Button type="submit" className="w-full" disabled={contactForm.submitting.value}>
            {contactForm.submitting.value && (
              <svg xmlns="http://www.w3.org/2000/svg" width="1.2em" height="1.2em" viewBox="0 0 24 24" class="mr-2">
                <path fill="currentColor" d="M18.364 5.636L16.95 7.05A7 7 0 1 0 19 12h2a9 9 0 1 1-2.636-6.364" />
              </svg>
            )}
            Absenden
          </Button>
        </Form>
      </div>
    </div>
  )
}

export default ContactForm

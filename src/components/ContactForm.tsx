import directus from '@/lib/directus'
import { createItem } from '@directus/sdk'
import type { FunctionComponent } from 'preact'
import { useState } from 'preact/hooks'
import Button from './ui/Button'
import { Input } from './ui/Input'
import { Textarea } from './ui/Textarea'

const ContactForm: FunctionComponent = () => {
  const [formData, setFormData] = useState({ name: '', email: '', message: '' })
  const [submitted, setSubmitted] = useState(false)

  const handleChange = (e: Event) => {
    const target = e.target as HTMLInputElement
    setFormData({ ...formData, [target.name]: target.value })
  }

  const handleSubmit = async (e: SubmitEvent) => {
    e.preventDefault()
    try {
      await directus.request(createItem('contact_requests', { ...formData }))
    } catch (error) {
      console.log(error)
    }
    setSubmitted(true)
  }

  return (
    <div>
      <div className="mx-auto w-full max-w-lg">
        {submitted ? (
          <div className="border-2 border-l-8 border-green-500 bg-green-300 p-4 text-center font-semibold dark:border-green-600 dark:bg-green-800">
            Danke für deine Nachricht! Ich melde mich bald bei dir.
          </div>
        ) : (
          <form className="space-y-8" onSubmit={handleSubmit}>
            <div>
              <label htmlFor="name" className="sr-only mb-2 block text-sm font-medium">
                Name
              </label>
              <Input
                name="name"
                id="name"
                placeholder="Name"
                value={formData.name}
                onChange={handleChange}
                required
                className="w-full"
              />
            </div>
            <div>
              <label htmlFor="email" className="sr-only mb-2 block text-sm font-medium">
                E-Mail
              </label>
              <Input
                id="email"
                type="email"
                placeholder="E-Mail"
                name="email"
                value={formData.email}
                onChange={handleChange}
                required
              />
            </div>
            <div>
              <label htmlFor="message" className="sr-only mb-2 block text-sm font-medium">
                Nachricht
              </label>
              <Textarea
                name="message"
                id="message"
                placeholder="Nachricht"
                value={formData.message}
                onChange={handleChange}
                required
              />
            </div>
            <Button type="submit" className="w-full">
              Absenden
            </Button>
          </form>
        )}
      </div>
    </div>
  )
}

export default ContactForm

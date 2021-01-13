import Editor from './editor'
import { canUser } from 'utils'
import {
  useParams,
} from 'react-router-dom'

export default () => {

  const { id } = useParams()

  const canUserEdit = canUser('create' )

  if (!canUserEdit) {
    return <p>{ 'Cheating!' }</p>
  }

  return (
    <>
      <Editor id={ id }/>
    </>
  )
}
